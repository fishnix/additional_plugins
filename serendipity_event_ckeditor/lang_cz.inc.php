<?php # 

/**
 *  @file lang_cz.inc.php 1.1 2013-06-22 11:15:15 VladaAjgl
 *  @version 1.1
 *  @author Vladim�r Ajgl <vlada@ajgl.cz>
 *  @translated 2013/06/22
 */

@define('PLUGIN_EVENT_CKEDITOR_NAME', 'CKEditor');
@define('PLUGIN_EVENT_CKEDITOR_DESC', 'Pou��v� CKEditor jako v�choz� WYSIWYG editor. Tento editor je aktu�ln�m state-of-art. Pou�it�: Doporu�eno! Po instalaci p�ejd�te na str�nku s nastaven�m tohoto pluginu a �t�te dal�� instrukce.');
@define('PLUGIN_EVENT_CKEDITOR_INSTALL', '<h2>Instalace</h2>
<ol style="line-height: 1.6">
<li>V nastaven� pluginu zadejte relativn� HTTP cestu k adres��i <em>"ckeditor"</em>.
    <div><strong>Pozn�mka:</strong> ve v�t�in� instalac� je tato cesta <em>"plugins/serendipity_event_ckeditor/ckeditor/"</em></div>
</li>
<li>V nastaven� pluginu zadejte plnou HTTP cestu k serendipity adres��i <em>"plugins"</em> (v�etn� ukon�uj�c�ho lom�tka).
    <div><strong>Pozn�mka:</strong> ve v�t�in� instalac� je tato cesta <em>"' . $serendipity['serendipityHTTPPath'] . 'plugins/"</em></div>
</li>
<li>Abyste umo�nili ostatn�m u�ivatel�m pou�it� CKEditoru, um�st�te tento plugin (CKEditor) bl�zko konce seznamu plugin�.</li>
<li>Ujist�te se, �e m�te v osobn�m nastaven� zapnuto pou�it� WYSIWYG m�du.</li>
</ol>

<h3>Aktualizace</h3>
<p>Tento plugin bude �as od �asu umo��ovat aktualizace pomoc� pluginu Spartacus.<hr>
Pokud v�bec n�kdy budete pot�ebovat ru�n� aktualizovat dodan� CKEditor bal��ky na vlastn� bal��ky (*), pak pros�m:
<ol style="line-height: 1.6">
<li><a href="http://ckeditor.com/download" target="_blank">st�hn�te CKEditor</a></li>
<li>Rozbalte jej do: <em>' . dirname(__FILE__) . '</em> (m�l by b�t vytvo�en podadres�� <em>"ckeditor"</em>)</li>
</ol>
(*) <em><strong>Pozn�mka:</strong> Toto vypnete (p�ep��e) integraci KCFinderu p�idanou na konec souboru ckeditor/config.js: <a style="border:0; text-decoration: none;" href="#" onClick="showConfig(\'el1\'); return false" title="TOGGLE_OPTION"><img src="'.serendipity_getTemplateFile('img/plus.png').'" id="optionel1" alt="+/-" border="0">&nbsp;TOGGLE_OPTION</a></em>
<div id="el1" style="margin-top: 0.5em; border: 1px solid #BBB;background-color: #EEE; padding: 0.5em">
<pre>
/* KCFinder integration - 2013-05-04 */
CKEDITOR.editorConfig = function(config) {
   config.filebrowserBrowseUrl = CKEDITOR_BASEPATH + \'../kcfinder/browse.php?type=files\';
   config.filebrowserImageBrowseUrl = CKEDITOR_BASEPATH + \'../kcfinder/browse.php?type=images\';
   config.filebrowserFlashBrowseUrl = CKEDITOR_BASEPATH + \'../kcfinder/browse.php?type=flash\';
   config.filebrowserUploadUrl = CKEDITOR_BASEPATH + \'../kcfinder/upload.php?type=files\';
   config.filebrowserImageUploadUrl = CKEDITOR_BASEPATH + \'../kcfinder/upload.php?type=images\';
   config.filebrowserFlashUploadUrl = CKEDITOR_BASEPATH + \'../kcfinder/upload.php?type=flash\';
};
</pre>
</div><script type="text/javascript" language="JavaScript">document.getElementById("el1").style.display = "none";</script>
</p>');
@define('PLUGIN_EVENT_CKEDITOR_CONFIG', '');
@define('PLUGIN_EVENT_CKEDITOR_INSTALL_PLUGPATH', 'HTTP cesta do serendipity dres��e s pluginy');
@define('PLUGIN_EVENT_CKEDITOR_CKEACF_OPTION', 'Vypnout "Pokro�il� fitrlov�n� obsahu" (tzv. ACF = Advanced-Content-Filter)');
@define('PLUGIN_EVENT_CKEDITOR_TBLB_OPTION', 'Pou��t v�choz� dvou��dkov� zobrazen� n�strojov� li�ty');