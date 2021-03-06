<?php # 


if (IN_serendipity !== true) {
    die ("Don't hack!");
}

// Probe for a language include with constants. Still include defines later on, if some constants were missing
$probelang = dirname(__FILE__) . '/' . $serendipity['charset'] . 'lang_' . $serendipity['lang'] . '.inc.php';
if (file_exists($probelang)) {
    include $probelang;
}

include_once dirname(__FILE__) . '/lang_en.inc.php';

class serendipity_event_multilingual extends serendipity_event
{
    var $tags  = array();
    var $title = PLUGIN_EVENT_MULTILINGUAL_TITLE;
    var $showlang = '';
    var $switch_keys = array('title', 'body', 'extended');

    function introspect(&$propbag)
    {
        global $serendipity;

        $propbag->add('name',          PLUGIN_EVENT_MULTILINGUAL_TITLE);
        $propbag->add('description',   PLUGIN_EVENT_MULTILINGUAL_DESC);
        $propbag->add('stackable',     false);
        $propbag->add('author',        'Garvin Hicking, Wesley Hwang-Chung');
        $propbag->add('requirements',  array(
            'serendipity' => '0.8',
            'smarty'      => '2.6.7',
            'php'         => '4.1.0'
        ));
        $propbag->add('groups', array('FRONTEND_ENTRY_RELATED', 'BACKEND_EDITOR'));
        $propbag->add('version',       '2.14');
        $propbag->add('configuration', array('copytext', 'placement', 'tagged_title', 'tagged_entries', 'tagged_sidebar'));
        $propbag->add('event_hooks',    array(
            'frontend_fetchentries'                             => true,
            'frontend_fetchentry'                               => true,
            'entry_display'                                     => true,
            'backend_publish'                                   => true,
            'backend_save'                                      => true,
            'backend_display'                                   => true,
            'frontend_entryproperties'                          => true,
            'backend_sidebar_entries'                           => true,
            'external_plugin'                                   => true,
            'css'                                               => true,
            'backend_entryform'                                 => true,
            'backend_entry_presave'                             => true,
            'backend_entry_updertEntry'                         => true,
            'frontend_entries_rss'                              => true,
            'frontend_comment'                                  => true,
            'frontend_sidebar_plugins'                          => true,
            'genpage'                                           => true,
        ));
        $this->supported_properties = array('lang_selected','lang_display');
        $this->dependencies = array('serendipity_plugin_multilingual' => 'remove');

        // Okay, Garv. I explain this to you ONCE and ONLY.
        // $this->lang_display is the variable that FORCES translations of entries. If a translation does not exist,
        //                     an entry is NOT SHOWN.
        // $this->showlang     is the variable that indicates which language of an entry to prefer.
        if (isset($serendipity['GET']['lang_display'])) {
            $this->lang_display = serendipity_db_escape_string($serendipity['GET']['lang_display']);
            header('X-Serendipity-ML-LD-1: ' . $this->cleanheader($this->lang_display));
        }

        if (empty($this->showlang) && isset($serendipity['POST']['properties']['lang_selected'])) {
            $this->showlang = serendipity_db_escape_string($serendipity['POST']['properties']['lang_selected']);
            $_SESSION['last_lang'] = $this->showlang;
            serendipity_header('X-Serendipity-ML-SL-1: ' . $this->cleanheader($this->showlang));
        } elseif (empty($this->showlang) && isset($serendipity['GET']['lang_selected'])) {
            $this->showlang = serendipity_db_escape_string($serendipity['GET']['lang_selected']);
            $_SESSION['last_lang'] = $this->showlang;
            serendipity_header('X-Serendipity-ML-SL-2: ' . $this->cleanheader($this->showlang));
        } elseif (empty($this->showlang) && isset($_REQUEST['user_language'])) {
            $this->showlang     = $_REQUEST['user_language'];
            serendipity_header('X-Serendipity-ML-SL-3: ' . $this->cleanheader($this->showlang));
        } elseif (empty($this->showlang) && isset($_REQUEST['serendipity']['serendipityLanguage'])) {
            $this->showlang     = $_REQUEST['serendipity']['serendipityLanguage'];
            serendipity_header('X-Serendipity-ML-SL-4: ' . $this->cleanheader($this->showlang));
        } elseif (empty($this->showlang) && isset($serendipity['lang']) && !isset($_SESSION['last_lang'])) {
           $this->showlang     = $serendipity['lang'];
           serendipity_header('X-Serendipity-ML-SL-5: ' . $this->cleanheader($this->showlang));
        }

        if (!isset($serendipity['languages'][$this->showlang])) {
            $this->showlang = '';
            serendipity_header('X-Serendipity-ML-SL-RESET: ' . $this->cleanheader($serendipity['default_lang']));
        }

        if (!headers_sent()) {
            serendipity_header('X-Serendipity-ContentLang: ' . $this->cleanheader($this->showlang));
        }

        $this->setupDB();
    }

    function setupDB()
    {
        global $serendipity;

        $built = $this->get_config('db_built', null);
        if (empty($built)) {
            $q = "@CREATE {FULLTEXT_MYSQL} INDEX fulltext_idx on {$serendipity['dbPrefix']}entryproperties (value);";
            serendipity_db_schema_import($q);
            $this->set_config('db_built', 2);
        }
    }

    function cleanheader($string) {
        $string = preg_replace('@[^0-9a-z_-]@imsU', '', $string);
    }

    function introspect_config_item($name, &$propbag)
    {
        switch($name) {
            case 'tagged_title':
                $propbag->add('type',        'boolean');
                $propbag->add('name',        PLUGIN_EVENT_MULTILINGUAL_TAGTITLE);
                $propbag->add('description', PLUGIN_EVENT_MULTILINGUAL_TAGTITLE_DESC);
                $propbag->add('default',     'true');
                break;

            case 'tagged_entries':
                $propbag->add('type',        'boolean');
                $propbag->add('name',        PLUGIN_EVENT_MULTILINGUAL_TAGENTRIES);
                $propbag->add('description', PLUGIN_EVENT_MULTILINGUAL_TAGENTRIES_DESC);
                $propbag->add('default',     'false');
                break;

            case 'tagged_sidebar':
                $propbag->add('type',        'boolean');
                $propbag->add('name',        PLUGIN_EVENT_MULTILINGUAL_TAGSIDEBAR);
                $propbag->add('description', PLUGIN_EVENT_MULTILINGUAL_TAGSIDEBAR_DESC);
                $propbag->add('default',     'true');
                break;

            case 'placement':
                $propbag->add('type',        'radio');
                $propbag->add('name',        PLUGIN_EVENT_MULTILINGUAL_PLACE);
                $propbag->add('description', '');
                $propbag->add('radio',        array(
                    'value' => array('add_footer', 'multilingual_footer'),
                    'desc'  => array(PLUGIN_EVENT_MULTILINGUAL_PLACE_ADDFOOTER, PLUGIN_EVENT_MULTILINGUAL_PLACE_ADDSPECIAL)
                ));
                $propbag->add('radio_per_row', '1');
                $propbag->add('default',     'add_footer');
                break;

            case 'copytext':
                $propbag->add('type',        'boolean');
                $propbag->add('name',        PLUGIN_EVENT_MULTILINGUAL_COPY);
                $propbag->add('description', PLUGIN_EVENT_MULTILINGUAL_COPYDESC);
                $propbag->add('default',     'true');
                break;
            default:
                    return false;
        }
        return true;
    }

    function generate_content(&$title) {
        $title = $this->title;
    }

    function &getLang($id, &$properties) {
        global $serendipity;
        static $default_lang = null;
        static $false = false;
        static $true  = true;

        $langs = array();
        // list/each can use references
        if (!is_array($properties)) {
            return $false;
        }

        while(list($key,) = each($properties)) {
            preg_match('@^multilingual_body_(.+)$@', $key, $match);
            if (isset($match[1])) {
                $langs[] = '<a class="multilingual_' . $match[1] . '" href="' . $serendipity['serendipityHTTPPath'] . $serendipity['indexFile'] . '?' . serendipity_archiveURL($id, $serendipity['languages'][$match[1]], 'serendipityHTTPPath', false) . '&amp;serendipity[lang_selected]=' . $match[1] . '">' . $serendipity['languages'][$match[1]] . '</a>';
            }
        }

        if (count($langs) < 1) {
            return $false;
        }

        // retrieve the default language of the blog...
        if ($default_lang === null) {
            if (isset($serendipity['default_lang'])) {
                $default_lang = $serendipity['languages'][$serendipity['default_lang']];
            } else {
                $default_lang_sql = serendipity_db_query("SELECT value FROM {$serendipity['dbPrefix']}config WHERE name = 'lang'", true, 'assoc');
                if (is_array($default_lang_sql)) {
                    $default_lang     = $serendipity['languages'][$default_lang_sql['value']];
                } else {
                    $default_lang     = USE_DEFAULT;
                }
            }
        }

        $langs[] = '<a class="multilingual_default multilingual_' . $default_lang . '" href="' . $serendipity['serendipityHTTPPath'] . $serendipity['indexFile'] . '?' . serendipity_archiveURL($id, 'Default', 'serendipityHTTPPath', false) . '&amp;serendipity[lang_selected]=default">' . $default_lang . '</a>';
        $lang = implode(', ', $langs);
        return $lang;
    }

    //function neglang($lang) {
     function neglang($lang, $assert = '?!') {
        /* Creates the negation pattern from a two letter language identifier. */
        // Negative look ahead assertion. ".*" is used because any letter except of the language string shall be allowed, without it, nothing woud ever match */
        return '(' . $assert . $lang . ').*';
        //return '(?!' . $lang . ').*';
         
        //return '[^'.$lang[0].'][^'.$lang[1].']';
    }

    function strip_langs($msg) {
        global $serendipity;
        
        if (!preg_match('@{{@', $msg)) return $msg;

        $language = $serendipity['lang'];
        /* Handle escaping of {} chars. If someone is up for it,
           they're welcome to try and find a better way. As it is,
           this appears to work. */
        $msg = str_replace('\{', chr(1), $msg);
        $msg = str_replace('\}', chr(2), $msg);
        
        // The explode actually makes sure that each latter array part will end on either the full string end or {{--}}. {{--}} will also never be contained inside the string, so we don't need to rule it out any longer.
        $parts = explode('{{--}}', $msg);
        $out   = '';
        // Iterate each subblock and inspect if its language matches.
        foreach($parts AS $idx => $match) {
            if (empty($match)) continue; // Last block part, skip it.
            if (stristr($match, '{{!' . $serendipity['lang'] . '}}')) {
                // Current language found. Keep the string, minus the {{!xx}} part.
                $out .= preg_replace('@\{\{!' . $serendipity['lang'] . '\}\}@', '', $match);
            } else {
                // Current language not found. Remove everything after {{!xx}}.
                $out .= preg_replace('@\{\{![^\}]+\}\}.+$@', '', $match);
            }
        }

        $msg = $out;

        /* Put back escaped {} chars */
        $msg = str_replace(chr(1), '{', $msg);
        $msg = str_replace(chr(2), '}', $msg);

        return $msg;
    }

    function tag_title() {
        global $serendipity;

                    if (serendipity_db_bool($this->get_config('tagged_title', 'true'))) {
                    
                        if ($serendipity['smarty']) {
                            $serendipity['smarty']->assign('blogTitle',$this->strip_langs($serendipity['blogTitle']));
                            $serendipity['smarty']->assign('blogDescription',$this->strip_langs($serendipity['blogDescription']));
                            $head_title = $serendipity['smarty']->get_template_vars('head_title');
                            if (!empty($head_title)) {
                                $serendipity['smarty']->assign('head_title',$this->strip_langs($head_title));
                            }

                            $head_subtitle = $serendipity['smarty']->get_template_vars('head_subtitle');
                            if (!empty($head_subtitle)) {
                                $serendipity['smarty']->assign('head_subtitle',$this->strip_langs($head_subtitle));
                            }
                        } else {
                            $serendipity['blogTitle'] = $this->strip_langs($serendipity['blogTitle']);
                            $serendipity['blogDescription'] = $this->strip_langs($serendipity['blogDescription']);
                        }    
                    }    
    }
    
    function event_hook($event, &$bag, &$eventData, $addData = null) {
        global $serendipity;

        $hooks = &$bag->get('event_hooks');
        if (isset($hooks[$event])) {
            switch($event) {

                case 'backend_entry_updertEntry':
                    if (isset($serendipity['POST']['no_save'])) {
                            $eventData["error"] = true;
                    }    
                    return true;
                    break;

                case 'backend_entry_presave':
                    if (!isset($serendipity['POST']['properties']) || !is_array($serendipity['POST']['properties']) || !isset($eventData['id']) || empty($serendipity['POST']['properties']['lang_selected'])) {
                        return true;
                    }

                    // Restore native language version, ONLY if a different language is being submitted.
                    $restore = serendipity_db_query("SELECT title, body, extended FROM {$serendipity['dbPrefix']}entries WHERE id = " . (int)$eventData['id']);
                    if (is_array($restore)) {
                        foreach($restore AS $row) {
                            foreach($this->switch_keys AS $restorekey) {
                                $eventData[$restorekey] = $row[$restorekey];
                            }
                        }
                    }
                    break;

                case 'backend_publish':
                case 'backend_save':
                    if (!isset($serendipity['POST']['properties']) || !is_array($serendipity['POST']['properties']) || !isset($eventData['id']) || empty($serendipity['POST']['properties']['lang_selected'])) {
                        return true;
                    }

                    $ls = &$serendipity['POST']['properties']['lang_selected'];

                    $this->supported_properties[] = 'multilingual_title_' . $ls;
                    $serendipity['POST']['properties']['multilingual_title_' . $ls]    = $serendipity['POST']['title'];

                    $this->supported_properties[] = 'multilingual_body_' . $ls;
                    $serendipity['POST']['properties']['multilingual_body_' . $ls]     = $serendipity['POST']['body'];

                    $this->supported_properties[] = 'multilingual_extended_' . $ls;
                    $serendipity['POST']['properties']['multilingual_extended_' . $ls] = $serendipity['POST']['extended'];

                    // Get existing data
                    $property = serendipity_fetchEntryProperties($eventData['id']);

                    foreach($this->supported_properties AS $prop_key) {
                        $prop_val = (isset($serendipity['POST']['properties'][$prop_key]) ? $serendipity['POST']['properties'][$prop_key] : null);
                        if (!isset($property[$prop_key]) && !empty($prop_val)) {
                            $q = "INSERT INTO {$serendipity['dbPrefix']}entryproperties (entryid, property, value) VALUES (" . (int)$eventData['id'] . ", '" . serendipity_db_escape_string($prop_key) . "', '" . serendipity_db_escape_string($prop_val) . "')";
                        } elseif ($property[$propkey] != $prop_val && !empty($prop_val)) {
                            $q = "UPDATE {$serendipity['dbPrefix']}entryproperties SET value = '" . serendipity_db_escape_string($prop_val) . "' WHERE entryid = " . (int)$eventData['id'] . " AND property = '" . serendipity_db_escape_string($prop_key) . "'";
                        } else {
                            $q = "DELETE FROM {$serendipity['dbPrefix']}entryproperties WHERE entryid = " . (int)$eventData['id'] . " AND property = '" . serendipity_db_escape_string($prop_key) . "'";
                        }

                        serendipity_db_query($q);
                    }

                    return true;
                    break;

                case 'genpage':
                    $this->tag_title();

                    if ($serendipity['smarty']) {
                        $serendipity['smarty']->register_modifier('multilingual_lang', array($this, 'strip_lang'));
                    }
                    return true;
                    break;

                case 'backend_entryform':
                    if (!empty($this->showlang)) {
                        // language is given (he wants a translation)
                        $props = serendipity_fetchEntryProperties($eventData['id']);
                        // this is a language change, not a save -- we want the DB values
                        // unless the user chooses to retain previous language content
                        if (isset($serendipity['POST']['no_save'])) {
                            foreach($this->switch_keys AS $key) {
                                if (!serendipity_db_bool($this->get_config('copytext', 'true')) || !empty($props['multilingual_' . $key . '_' . $this->showlang])) {
                                    $eventData[$key] = $props['multilingual_' . $key . '_' . $this->showlang];
                                }
                            }
                        }
                    } elseif (!empty($eventData['id'])) {
                        // language is NOT given (he wants the default language)
                        $props = serendipity_fetchEntry('id', $eventData['id'], 1, 1);
                        if (!is_array($props)) {
                            return true;
                        }
                        // this is a language change, not a save -- we want the DB values
                        if (isset($serendipity['POST']['no_save'])) {
                            foreach($this->switch_keys AS $key) {
                                $eventData[$key] = $props[$key];
                            }
                        }
                    }

                    return true;
                    break;

                case 'css':
                    if (strpos($eventData, '.serendipity_multilingualInfo')) {
                        // class exists in CSS, so a user has customized it and we don't need default
                        return true;
                    }
?>

.serendipity_multilingualInfo {
    margin-left: auto;
    margin-right: 0px;
    text-align: right;
    font-size: 7pt;
    display: block;
    margin-top: 5px;
    margin-bottom: 0px;
}

.serendipity_multilingualInfo a {
    font-size: 7pt;
    text-decoration: none;
}

.serendipity_multilingualInfo a:hover {
    color: green;
}
<?php
                    return true;
                    break;

                case 'entry_display':
                    if (!is_array($eventData)) {
                        return false;
                    }
                    $place = $this->get_config('placement', 'add_footer');
                    $msg = '<div class="serendipity_multilingualInfo">' . PLUGIN_EVENT_MULTILINGUAL_SWITCH . ': %s</div>';
                    if ($addData['extended'] || $addData['preview']) {
                        if ($langs = $this->getLang($eventData[0]['id'], $eventData[0]['properties'])) {
                            if (!empty($this->showlang)) {
                                $props = &$eventData[0]['properties'];
                                foreach($this->switch_keys AS $key) {
                                    if (!empty($props['multilingual_' . $key . '_' . $this->showlang])) {
                                        $eventData[0][$key] = $props['multilingual_' . $key . '_' . $this->showlang];
                                    }
                                }

                                unset($eventData[0]['properties']['ep_cache_body']);
                                unset($eventData[0]['properties']['ep_cache_extended']);
                            }

                            $eventData[0][$place] .= sprintf($msg, $langs);
                        }
                    } else {
                        $elements = count($eventData);

                        // Walk entry array and insert karma voting line.
                        for ($i = 0; $i < $elements; $i++) {
                            if (!isset($eventData[$i][$place])) {
                                $eventData[$i][$place] = '';
                            }

                            if (!empty($this->lang_display)) {
                                $this->showlang = $this->lang_display;
                            }

                            if (!empty($this->showlang)) {
                                // Not sure if it's the best way to get translations shown instead of the
                                // original entries

                                $props = &$eventData[$i]['properties'];
                                foreach($this->switch_keys AS $key) {
                                    if (!empty($props['multilingual_' . $key . '_' . $this->showlang])) {
                                        $eventData[$i][$key] = $props['multilingual_' . $key . '_' . $this->showlang];
                                    }
                                }
                                unset($eventData[$i]['properties']['ep_cache_body']);
                                unset($eventData[$i]['properties']['ep_cache_extended']);
                            }

                            if ($langs = $this->getLang($eventData[$i]['id'], $eventData[$i]['properties'])) {
                                $eventData[$i][$place] .= sprintf($msg, $langs);
                            }
                        }
                    }
                    // Tagged translation of Blog title and description
                    $this->tag_title();
                         
                    if (serendipity_db_bool($this->get_config('tagged_entries', 'true'))) {
                        foreach ($eventData as $key => $entry) {
                            if (isset($eventData[$key]['title'])) {
                              $eventData[$key]['title'] = $this->strip_langs($eventData[$key]['title']);
                              $eventData[$key]['body'] = $this->strip_langs($eventData[$key]['body']);
                              if (is_array($eventData[$key]['categories'])) {
                                foreach($eventData[$key]['categories'] AS $ec_key => $ec_val) {
                                  $eventData[$key]['categories'][$ec_key]['category_name'] = $this->strip_langs($ec_val['category_name']);
                                }
                              }
                            }  
                        }
                    }
                    return true;
                    break;

                case 'backend_display':
                    if (isset($serendipity['POST']['properties']['lang_selected'])) {
                        $lang_selected = $serendipity['POST']['properties']['lang_selected'];
                    } else {
                        $lang_selected = '';
                    }

                    $use_lang = $serendipity['languages'];
                    unset($use_lang[$serendipity['lang']]); // Unset 'default' language. Easier handling.

                    $langs = '';
                    foreach($use_lang AS $code => $desc) {
                        $langs .= '<option value="' . $code . '" ' . ($lang_selected == $code ? 'selected="selected"' : '') . '>' . htmlspecialchars($desc) . '</option>' . "\n";
                    }
?>
                    <fieldset style="margin: 5px">
                        <legend><?php echo PLUGIN_EVENT_MULTILINGUAL_TITLE; ?></legend>
<?php
                    if (isset($eventData['id'])) { ?>
                            <label for="serendipity[properties][lang_selected]"><?php echo PLUGIN_EVENT_MULTILINGUAL_CURRENT; ?></label><br />
                            <select name="serendipity[properties][lang_selected]" id="properties_lang_selected" />
                            <option value=""><?php echo USE_DEFAULT; ?></option>
                            <?php echo $langs; ?>
                            </select> <input class="serendipityPrettyButton input_button" type="submit" name="serendipity[no_save]" value="<?php echo PLUGIN_EVENT_MULTILINGUAL_SWITCH; ?>" />
<?php
                    } else {
                        echo PLUGIN_EVENT_MULTILINGUAL_NEEDTOSAVE;
                    }
?>
                    </fieldset>
<?php
                    return true;
                    break;

                case 'frontend_entryproperties':
                    if (class_exists('serendipity_event_entryproperties')) {
                        // Fetching of properties is already done there, so this is just for poor users who don't have the entryproperties plugin enabled
                        return true;
                    }
                    $q = "SELECT entryid, property, value FROM {$serendipity['dbPrefix']}entryproperties WHERE entryid IN (" . implode(', ', array_keys($addData)) . ") AND property LIKE '%multilingual_%'";
                    $properties = serendipity_db_query($q);
                    if (!is_array($properties)) {
                        return true;
                    }
                    foreach($properties AS $idx => $row) {
                        $eventData[$addData[$row['entryid']]]['properties'][$row['property']] = $row['value'];
                    }
                    return true;
                    break;

                case 'frontend_entries_rss':
                    if (is_array($eventData)) {
                        foreach($eventData AS $i => $entry) {
                            if (!empty($this->lang_display)) {
                                $this->showlang = $this->lang_display;
                            }

                            if (!empty($this->showlang)) {
                                // Not sure if it's the best way to get translations shown instead of the
                                // original entries

                                $props = &$eventData[$i]['properties'];
                                foreach($this->switch_keys AS $key) {
                                    if (!empty($props['multilingual_' . $key . '_' . $this->showlang])) {
                                        $eventData[$i][$key] = $props['multilingual_' . $key . '_' . $this->showlang];
                                    }
                                }
                                unset($eventData[$i]['properties']['ep_cache_body']);
                                unset($eventData[$i]['properties']['ep_cache_extended']);
                            }
                        }
                    }
                    if (serendipity_db_bool($this->get_config('tagged_entries', 'true'))) {
                        foreach ($eventData as $key => $entry) {
                            $eventData[$key]['title'] = $this->strip_langs($eventData[$key]['title']);
                            $eventData[$key]['body'] = $this->strip_langs($eventData[$key]['body']);
                        }
                    }
                    break;

                case 'frontend_fetchentries':
                case 'frontend_fetchentry':
                    if (!empty($this->lang_display)) {
                        $this->showlang = $this->lang_display;
                    }

                    if ($addData['source'] == 'search' && empty($this->showlang) && !empty($_SESSION['last_lang'])) {
                        header('X-SearchLangOverride: ' . $_SESSION['last_lang']);
                        $this->showlang = $_SESSION['last_lang'];
                    }

                    if (empty($this->showlang)) {
                        return;
                    }
                    $cond  = "multilingual_body.value AS multilingual_body,\n";
                    $cond .= "multilingual_extended.value AS multilingual_extended,\n";
                    $cond .= "multilingual_title.value AS multilingual_title,\n";
                    if (empty($eventData['addkey'])) {
                        $eventData['addkey'] = $cond;
                    } else {
                        $eventData['addkey'] .= $cond;
                    }
                    $cond  = "\nLEFT OUTER JOIN {$serendipity['dbPrefix']}entryproperties multilingual_body
                                                 ON (e.id = multilingual_body.entryid AND multilingual_body.property = 'multilingual_body_" . $this->showlang . "')";
                    $cond .= "\nLEFT OUTER JOIN {$serendipity['dbPrefix']}entryproperties multilingual_extended
                                                 ON (e.id = multilingual_extended.entryid AND multilingual_extended.property = 'multilingual_extended_" . $this->showlang . "')";
                    $cond .= "\nLEFT OUTER JOIN {$serendipity['dbPrefix']}entryproperties multilingual_title
                                                 ON (e.id = multilingual_title.entryid AND multilingual_title.property = 'multilingual_title_" . $this->showlang . "')";

                    if (!empty($this->lang_display)) {
                        // If lang_display is set - we want ONLY the entries which have translation
                        $eventData['and'] .= " AND multilingual_body.value IS NOT NULL";
                    }

                    if (empty($eventData['joins'])) {
                        $eventData['joins'] = $cond;
                    } else {
                        $eventData['joins'] .= $cond;
                    }

                    if ($addData['source'] == 'search' && isset($eventData['find_part'])) {
                        $term =& $addData['term'];
                        $cond =& $eventData;
                        if ($serendipity['dbType'] == 'postgres') {
                            $cond['find_part'] .= " OR (multilingual_body.value ILIKE '%$term%' OR multilingual_extended.value ILIKE '%$term%' OR multilingual_title.value ILIKE '%$term%')";
                        } elseif ($serendipity['dbType'] == 'sqlite') {
                            $term      = serendipity_mb('strtolower', $term);
                            $cond['find_part'] .= " OR (lower(multilingual_body.value) LIKE '%$term%' OR lower(multilingual_extended.value) LIKE '%$term%' OR lower(multilingual_title.value) LIKE '%$term%')";
                        } else {
                            if (preg_match('@["\+\-\*~<>\(\)]+@', $term)) {
                                $bool = ' IN BOOLEAN MODE';
                            } else {
                                $bool = '';
                            }
                            $cond['find_part'] .= " OR (
                                                         MATCH(multilingual_body.value)        AGAINST('$term' $bool)
                                                         OR MATCH(multilingual_extended.value) AGAINST('$term' $bool)
                                                         OR MATCH(multilingual_title.value)    AGAINST('$term' $bool)
                                                       )";
                        }

                    }
                    return true;
                    break;
                case 'frontend_comment':
                    if (serendipity_db_bool($this->get_config('tagged_entries', 'true'))) {
                        $serendipity['smarty']->assign('head_title', $this->strip_langs($serendipity['head_title']));
                    }
                    if (serendipity_db_bool($this->get_config('tagged_title', 'true'))) {
                        $serendipity['smarty']->assign('head_subtitle', $this->strip_langs($serendipity['head_subtitle']));
                    }
                    return true;
                    break;
                case 'frontend_sidebar_plugins':
                    if (serendipity_db_bool($this->get_config('tagged_sidebar', 'true'))) {
                        foreach ($eventData as $key => $entry) {
                            $eventData[$key]['title'] = $this->strip_langs($eventData[$key]['title']);
                            $eventData[$key]['content'] = $this->strip_langs($eventData[$key]['content']);
                        }
                    }
                    return true;
                    break;

                default:
                    return false;
                    break;
            }
        } else {
            return false;
        }
    }
}

/* vim: set sts=4 ts=4 expandtab : */
