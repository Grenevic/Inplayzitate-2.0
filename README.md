<h2>Inplayzitate 2.0</h2>
Mit diesem Plugin haben ausgewählte Usergruppen die Möglichkeit, Zitate aus dem RPG-Ingame einzusenden. Diese Zitate werden dann im Foren-Index und auf einer Übersichtsseite angezeigt.

<ul>
<li> Eintragen von Zitaten
<li> Automatisches Speichern von Charakter & Szene
<li> Übersichtsseite, die nach Zeitpunkt und Charakter gefiltert werden
<li> Löschen von Zitaten im Frontend möglich
<li> Einstellbar, welche Usergruppen zitieren dürfen
</ul>


<h1>Plugin funktionsfähig machen</h1>
<ul>
<li>Die Plugin-Datei ladet ihr in den angegebenen Ordner <b>inc/plugins</b> hoch.
<li>Die Language-Dateien ladet ihr in den entsprechenden Sprachordner.
<li>Das Plugin muss nun im Admin CP unter <b>Konfiguration - Plugins</b> installiert und aktiviert werden
<li>In den Foreneinstellungen findet ihr nun - ganz unten - Einstellungen zu "Inplayzitate". Macht dort eure gewünschten Einstellungen.
</ul><br />

Das Plugin ist nun einsatzbereit. Solltet ihr schon einiges an eurem Forum gemacht haben, und nicht wie ich im Testdurchlauf ein Default-Theme verwenden, kann es sein, dass nicht alle Variablen eingefügt werden. Sollte euch eine Anzeige fehlen, könnt ihr auf folgende Variablen zurückgreifen:

<blockquote>{$inplayquotes}  // Link zur Zitate-Box
* ruft index_inplayquotes auf
** ACHTUNG: wird NICHT angezeigt, wenn kein Zitat vorhanden

{$post['inplayquotes']} // Button in Postbit
* ruft postbit_inplayquotes auf
</blockquote>

<h1>Template-Änderungen</h1>
Folgende Templates werden durch dieses Plugin <i>neu hinzugefügt</i>:

<ul>
<li>index_inplayquotes
  
<li>postbit_inplayquotes
  
<li>misc_inplayquotes_add
<li>misc_inplayquotes_overview
<li>misc_inplayquotes_overview_bit
<li>misc_inplayquptes_overview_bit_delete
</ul>

Folgende Templates werden durch dieses Plugin <i>bearbeitet</i>:
<ul>
<li>index
<li>postbit
<li>postbit_classic
</ul>

<h1>Demo</h1><br />
<center>

<img src="http://eightletters.de/plugins/screens/addquote.png" /><br />
http://eightletters.de/plugins/screens/addquote.png<br /><br />

<img src="http://eightletters.de/plugins/screens/indexquote.png" /><br />
http://eightletters.de/plugins/screens/indexquote.png<br /><br />

<img src="http://eightletters.de/plugins/screens/overview.png" /><br />
http://eightletters.de/plugins/screens/overview.png<br /><br />

</center>

<h1>Anmerkungen</h1>
<ul>
<li> Ich habe an meine eigenen Plugins nicht den Anspruch, sie "professionell" sein zu lassen. Ich bin sehr dankbar für Verbesserungsvorschläge per PN (damit das Thema nicht zugewühlt wird) und versuche diese auch umzusetzen, bitte aber gerade erfahrenere Programmierer hier darum, nicht allzu streng mit mir zu sein. Ich weiß: da ist noch Luft nach oben.
<li> <b>[color=red]Ich möchte nicht[/color]</b>, dass mein Plugin auf anderen Plattformen verbreitet wird. 
<li> <b>[color=red]Ich möchte nicht[/color]</b>, dass ihr mein Plugin auf eigene Faust erweitert und die Erweiterungen hier online stellt.
<li> <b>[color=red]Ich möchte nicht[/color]</b>, dass ihr mein Plugin auf Wunsch anderer modifiziert und/oder diese Modifizierung hier online stellt. 
<li><b>[color=green]Ich möchte[/color]</b>, dass ihr mir Bugs/Probleme so schnell wie möglich meldet, damit etwas dagegen getan werden kann. 
<li><b>[color=green]Ich möchte[/color]</b>, dass ihr dieses Thema hier gut durchlest, bevor ihr eine Frage stellt, die ich dann zum 10. Mal beantworten muss. 
</ul>

<h3>Tasse Kaffee spenden</h3>
Dir gefällt, was ich mache? Wenn du magst und kannst, würde ich mich sehr über ein Tässchen Kaffee freuen. c: Wenn nicht, ist das aber auch kein Problem! Trinkgeld bringt dir sogeschen keinen Vorteil und kein Trinkgeld bringt dir keinen Nachteil. <3 

<center> <a href='https://ko-fi.com/G2G7GXQQ' target='_blank'><img height='36' style='border:0px;height:36px;' src='https://az743702.vo.msecnd.net/cdn/kofi4.png?v=0' border='0' alt='Buy Me a Coffee at ko-fi.com' /></a></center>

[size=x-large]<center><a href="https://github.com/its-sparks-fly/Inplayzitate-2.0" target="blank">ZUM DOWNLOAD</a></center>[/size]
