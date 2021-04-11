<?php
class Backend_DiaryController extends Formation_Controller_Action_I18n
{
    protected $_modelClass = 'Backend_Model_Diary';
    protected $_urlArray = array(
        'module' => 'backend',
        'controller' => 'diary',
        'action' => Null
    );
    protected $_form = array(
        'create' => 'Backend_Form_DiaryCreate',
        'update' => 'Backend_Form_DiaryUpdate',
        'delete' => 'Backend_Form_DiaryDelete',
    );
    protected $_typeName = 'Diary Entry';
    protected $_indexListItem = array(
        'fields' => array('title', '_allTranslations', 'urlId'),
        'format' => '%1$s / %3$s (%2$s)',
    );
    /*
     * see protected function _cleanPost for more
     */
    protected $_cleanPostKeys = array(
        '__ALL__' => array()
    );

    protected function _setFrontUrlArr($action, $dcModel)
    {
        switch($action)
        {
            case 'update':
            default:
                $frontArr = array(
                    'module' => 'default',
                    'controller' => 'index',
                    'action' => 'archive',
                    'key' => $dcModel['urlId']
                );
                $this->view->frontUrlArr = $frontArr;
                break;
        }
        return True;
    }
/*
    public function indexAction()
    {
        throw new GC_Debug_Exception('Hullu');
    }
*/

    protected function _setUpForm(GC_DomForm_Subset $form)
    {
        $translate = GC_Translate::get();
        $values = array();

        $filterModel = new Backend_Model_Filter();
        $items = $filterModel->findAll();
        $options = array();

        foreach($items as $item)
        {
            $name = 'unknown';
            if(isset($item['Translation']) && count($item['Translation']))
            {
                $name = isset($item['Translation'][$this->_getEditingLanguage()])
                    ? $item['Translation'][$this->_getEditingLanguage()]['name']
                    : $item['Translation'][key($item['Translation'])]['name'];
            }

            $options[] = array(
                $item['id'],
                $name
            );
        }
        if(!empty($options))
        {
            $values['Filters'] = $options;
        }
        else
        {
            $form->remove('Filters', $form->namespace);
        }
        if(!empty($values))
        {
            $form->setDefaults($values, $form->namespace);
        }
    }
    protected function _setUpFormCreate(/* Backend_Form_DiaryCreate */$form)
    {
        if(!$form instanceof Backend_Form_DiaryCreate)
            throw new GC_Exception('Form must be a Backend_Form_DiaryCreate');
        $this->_setupForm($form);
    }
    protected function _setUpFormUpdate(/* Backend_Form_DiaryUpdate */$form)
    {
        if(!$form instanceof Backend_Form_DiaryUpdate)
            throw new GC_Exception('Form must be a Backend_Form_DiaryUpdate');
        $this->_setupForm($form);
    }

    public function mockupAction_disabled()
    {
        return;
        $data = array();
        $langs = Zend_Registry::getInstance()->allowedLocales;
        $titles = array(
            'mei Thai Tel',
            'Interestig Thing No.',
            'Remarkable',
            'Just to Mention',
            'An Essay about the beeing of a',
            'Secret Stories',
            'Tale from my Day',
            'Zombie Attack survival tactics',
            'Sometimes the need for a really long title arises from our heart and sole'
        );
        $teasers = array(
            'this is a text that teaches us interesting stuff about stuffing intersting',
            'i always wanted to say this realy, but i never new how to blub',
            'ad addadd add adad adadadddadddad add addadadadad',
            'roses are red violets are blue i dunno what to write and this is new',
            'Over 10000 Styles Of High Quality Brands: Rolex,Bvlgari,Chanel',
            'Sehr geehrte Damen und Herren, wir suchen zur Zeit.',
            'If you want your tool to be strong and fresh each day, use enlargement pills. We have found out the effective formula for enlargement pills, try them now.',
            'Best quality Swiss and JapaneseRep1ica FakeWatches and Rep1icaHandbags and designer bags. In our rep1icaWatch store you can buy best quality Rep1icaWatches with a great saving '
        );
        $content = array(
            '<h3 class="title">Return Values</h3>
  <p class="para">

   If you are picking only one entry, <span class="function"><b>array_rand()</b></span>
   returns the key for a random entry. Otherwise, it returns an array
   of keys for the random entries. This is done so that you can pick
   random keys as well as values out of the array.
  </p>
',
'<h2><span class="editsection">[<a href="/w/index.php?title=Double_Bass_Array&amp;action=edit&amp;section=1" title="Abschnitt bearbeiten: Allgemein">Bearbeiten</a>]</span> <span class="mw-headline" id="Allgemein">Allgemein</span></h2>
<p>Zur Realisierung werden mindestens zwei (je mehr, desto besser der DBA-Effekt) identische <a href="/wiki/Subwoofer" title="Subwoofer">Subwoofer</a> benötigt, von denen die Hälfte an der gleichen Raumseite wie die normalen Frontlautsprecher möglichst wandnah aufgestellt werden. Die andere Hälfte der Subwoofer wird an der gegenüberliegenden Wand platziert und dient dort als aktive Auslöschung der reflektierten Schallwelle.</p>
<p>Ein Double Bass Array hat nichts mit Mehrkanal-Musikwiedergabe (Raumklang) mit <a href="/wiki/5.1" title="5.1">5.1</a> oder 7.1 Kanälen wie bei <a href="/wiki/Dolby_Digital" title="Dolby Digital">Dolby Digital</a> oder <a href="/wiki/DTS" title="DTS">DTS</a> zu tun. Die nötige Elektronik wird von Wiedergabegeräten, welche die vorgenannten Kodierungen beherrschen, in der Regel nicht bereitgestellt. Einige Top-Modelle bieten allerdings zwei Vorverstärkerausgänge für Subwoofer, die mit getrennter Verzögerung (Delay) angesteuert werden können.</p>

<h2><span class="editsection">[<a href="/w/index.php?title=Double_Bass_Array&amp;action=edit&amp;section=2" title="Abschnitt bearbeiten: Funktionsweise">Bearbeiten</a>]</span> <span class="mw-headline" id="Funktionsweise">Funktionsweise</span></h2>
<h3><span class="editsection">[<a href="/w/index.php?title=Double_Bass_Array&amp;action=edit&amp;section=3" title="Abschnitt bearbeiten: Gitteranordnung">Bearbeiten</a>]</span> <span class="mw-headline" id="Gitteranordnung">Gitteranordnung</span></h3>
<div class="thumb tright">
<div class="thumbinner" style="width:202px;"><a href="/w/index.php?title=Datei:DBA4.PNG&amp;filetimestamp=20080825143011" class="image"><img alt="" src="http://upload.wikimedia.org/wikipedia/commons/thumb/f/f8/DBA4.PNG/200px-DBA4.PNG" width="200" height="157" class="thumbimage" /></a>
<div class="thumbcaption">
<div class="magnify"><a href="/w/index.php?title=Datei:DBA4.PNG&amp;filetimestamp=20080825143011" class="internal" title="vergrößern"><img src="http://bits.wikimedia.org/skins-1.5/common/images/magnify-clip.png" width="15" height="11" alt="" /></a></div>
Häufig realisierte Gitteranordnung mit vier Tieftönern</div>
',
'<h2><span class="editsection">[<a href="/w/index.php?title=Elektrodynamischer_Lautsprecher&amp;action=edit&amp;section=4" title="Abschnitt bearbeiten: Mechanische Belastbarkeit">Bearbeiten</a>]</span> <span class="mw-headline" id="Mechanische_Belastbarkeit">Mechanische Belastbarkeit</span></h2>

<p>Die Membran kann durch zu große Auslenkungen mechanisch geschädigt werden. Dies tritt vor allem bei den tiefsten zulässigen Frequenzen auf. Dafür kann auch ein Sinussignal relevant sein. Bei Hoch- und Mitteltönern kann man zu große Auslenkungen meist am drastischen Ansteigen des Klirrens feststellen, für Tieftöner kann man das Erreichen der maximal zulässigen Auslenkung leicht messen. Leider werden diese Daten nie von den Herstellern angegeben, man kann sie jedoch meist aus anderen Daten berechnen. Typisch geht bei Hoch- und Mitteltönern durch die Frequenzweichen die mechanische Überlastung mit der thermischen einher. Eine Ausnahme sind Horntreiber. Diese sind für kleine Auslenkungen und große akustische Belastung entworfen. Ein Betrieb ohne diese, also unterhalb der Horngrenzfrequenz oder gar ohne Horn, kann zum sofortigen Ausfall trotz noch unkritischer Temperatur führen.</p>
<p>Für einen wirksamen Schutz von Tieftönern ist sowohl der thermische als auch der Auslenkungsgesichtspunkt zu beachten. Hohe Pegel lassen sich nur sinnvoll darstellen, wenn die Schutzvorrichtung auch die Wärmekapazität in Rechnung stellt. So kann z. B. ein Tieftöner durchaus für einige zehn Sekunden mit einer Leistungsaufnahme betrieben werden, die deutlich über der Dauerbelastungsangabe liegt. Die Schwingspule braucht Zeit, um sich aufzuwärmen. Die kleineren Antriebe von Hochtönern haben erheblich geringere Zeitkonstanten und bedürfen um so mehr der Vorsicht.</p>
<p>Gewarnt werden muss vor dem Irrglauben, man könne Lautsprecher durch leistungsschwache Verstärker vor Überlastung schützen: Bei <a href="/wiki/%C3%9Cbersteuerung" title="Übersteuerung" class="mw-redirect">Übersteuerung</a> (<a href="/wiki/Clipping_(Signalverarbeitung)" title="Clipping (Signalverarbeitung)" class="mw-redirect">Clipping</a>) erzeugen diese Klirrprodukte vor allem im höheren Frequenzbereich, die bei Mehr-Wege-Lautsprechern häufig zur Zerstörung des Hochtöners auch hoch belastbarer Boxen führen. Es ist dennoch sinnvoll, die Verstärkerleistung geringer als die Lautsprecher-Belastbarkeit zu wählen, da dann die Wiedergabequalität höher ist – vorausgesetzt, die Leistung liegt unterhalb der Verstärker-Grenzwerte.</p>
<p>Aus der Angabe einer zulässigen Spitzenleistung kann man – mit dem in den technischen Angaben aufgeführten Wirkungsgrad – einen maximal erzielbaren <a href="/wiki/Schalldruck" title="Schalldruck">Schalldruck</a> errechnen. In der Praxis wird der Schalldruck jedoch oft durch Kompression und Verzerrungen auf einen niedrigeren Wert begrenzt, da die <a href="/wiki/Schwingspule" title="Schwingspule">Schwingspule</a> den Bereich des homogenen Magnetfeldes verlässt und die Membraneinspannung mechanische Grenzen setzt. Die Angabe einer Spitzenleistung „<a href="/wiki/PMPO" title="PMPO" class="mw-redirect">PMPO</a>“, wie sie bei Lautsprechern der untersten Preisklasse zu finden ist, folgt keiner geschützten Definition und besitzt keine Aussagekraft.</p>
',
'<p>The fine arts display at the <a href="/wiki/Garden_Palace" title="Garden Palace">Sydney International Exhibition of 1879-1880</a> became the nucleus of a government collection administered by the <a href="/w/index.php?title=Royal_Art_Society_of_New_South_Wales&amp;action=edit&amp;redlink=1" class="new" title="Royal Art Society of New South Wales (page does not exist)">Royal Art Society of New South Wales</a>. However, most of the collection was destroyed in the <a href="/wiki/Garden_Palace" title="Garden Palace">Garden Palace Fire</a> of 1882, and the Art Society along with the trustees for the Academy of Art (formed in 1871) spent the next thirteen years debating with the state government, the press and the public, the need for a permanent gallery, its site, and the architect to build it. The Academy of Art trustees preferred a private architect, whereas the government want the assignment to be given to the <a href="/wiki/New_South_Wales_Government_Architect" title="New South Wales Government Architect">Colonial Architect</a>.<sup id="cite_ref-book_0-1" class="reference"><a href="#cite_note-book-0"><span>[</span>1<span>]</span></a></sup></p>

<p>By the time the site was agreed upon in 1895, <a href="/wiki/James_Barnet" title="James Barnet">James Barnet</a> had retired, and the new Colonial Architect, <a href="/wiki/Walter_Liberty_Vernon" title="Walter Liberty Vernon">Walter Liberty Vernon</a> (1846-1914), was given the assignment. As a temporary measure, John Horbury Hunt, a private architect, had designed a small brick structure to temporarily house the collection, which was built in 1885. This building was dwarfed by the new gallery when it opened in 1897 and remained to the rear of the new gallery until it was demolished in 1969 to make way for new extensions. Although the majority of Vernon\'s buildings are in the <a href="/wiki/Arts_and_Crafts_movement" title="Arts and Crafts movement" class="mw-redirect">Arts and Crafts style</a>, the 1897 building was built in the classical tradition. The Gallery\'s design was conservative and was the penultimate example of the neo-Greek temple as a portico for a major public institution in Sydney.<sup id="cite_ref-book_0-2" class="reference"><a href="#cite_note-book-0"><span>[</span>1<span>]</span></a></sup></p>
<p>The first two picture galleries were opened in 1897 and a further two in 1899. A watercolour gallery was added in 1901 and in 1902 the Grand Oval Lobby was completed. Outside the building, the names of old grand masters are emblazoned upon the front facade. In the panels beneath, bronze relief sculptures symbolise the contribution to art by four civilisations - Roman, Greek, Assyrian and Egyptian. On the main facade two remain empty, on the others all are empty.<sup id="cite_ref-book_0-3" class="reference"><a href="#cite_note-book-0"><span>[</span>1<span>]</span></a></sup></p>

<p>In 1958 a new "Art Gallery of New South Wales Act 1958" was passed and the Gallery\'s name was reverted to <i>The Art Gallery of New South Wales</i>. In 1968 the <a href="/wiki/New_South_Wales_Government" title="New South Wales Government" class="mw-redirect">New South Wales Government</a> decided that the Gallery would be extended and form a major part of the Captain Cook Bicentenary celebrations. As a result, the "Captain Cook wing" is built and opened to public in November 1970.<sup id="cite_ref-building_1-0" class="reference"><a href="#cite_note-building-1"><span>[</span>2<span>]</span></a></sup> The new gallery space provided five stories behind the original classical façade, increased the racking space to 1.25 linear kilometres, included a new café, a sculpture courtyard and administrative offices.<sup id="cite_ref-2" class="reference"><a href="#cite_note-2"><span>[</span>3<span>]</span></a></sup> Grey toned rough concrete was used to "blend" with the sandstone of the old building.</p>

<p>As part of the Bicentenary celebrations in 1988, a further eastern extension doubled the size of the Gallery.<sup id="cite_ref-building_1-1" class="reference"><a href="#cite_note-building-1"><span>[</span>2<span>]</span></a></sup> Both extensions were the responsibility of Government architect Andrew Andersons.<sup id="cite_ref-building_1-2" class="reference"><a href="#cite_note-building-1"><span>[</span>2<span>]</span></a></sup> More recently, as part of the "Open Museum" project, sculptures have been positioned along the entry road and on 23 October 2003 a new Asian Arts wing was opened.</p>
',
'<p>Throughout Australian history, NSW sporting teams have been very successful in both winning domestic competitions and providing players to the Australian national teams. The <a href="/wiki/NSW_Blues" title="NSW Blues" class="mw-redirect">NSW Blues</a> play in the <a href="/wiki/Ford_Ranger_Cup" title="Ford Ranger Cup" class="mw-redirect">Ford Ranger Cup</a> and <a href="/wiki/Sheffield_Shield" title="Sheffield Shield">Sheffield Shield</a> cricket competitions, the <a href="/wiki/NSW_Waratahs" title="NSW Waratahs" class="mw-redirect">NSW Waratahs</a> in the <a href="/wiki/Super_14" title="Super 14">Super 14</a> rugby union competition and <a href="/wiki/New_South_Wales_state_rugby_league_team" title="New South Wales state rugby league team" class="mw-redirect">The \'Blues\'</a> represent NSW in the annual <a href="/wiki/Rugby_League_State_of_Origin" title="Rugby League State of Origin">Rugby League State of Origin</a> series.</p>

<p>As well as the <a href="/wiki/State_of_Origin" title="State of Origin">State of Origin</a>, the headquarters of the <a href="/wiki/Australian_Rugby_League" title="Australian Rugby League">Australian Rugby League</a> and <a href="/wiki/National_Rugby_League" title="National Rugby League">National Rugby League</a> (NRL) are in Sydney, which is home to 9 of the 16 <a href="/wiki/National_Rugby_League" title="National Rugby League">National Rugby League</a> (NRL) teams. (<a href="/wiki/Sydney_Roosters" title="Sydney Roosters">Sydney Roosters</a>, <a href="/wiki/South_Sydney_Rabbitohs" title="South Sydney Rabbitohs">South Sydney Rabbitohs</a>, <a href="/wiki/Parramatta_Eels" title="Parramatta Eels">Parramatta Eels</a>, <a href="/wiki/Cronulla-Sutherland_Sharks" title="Cronulla-Sutherland Sharks">Cronulla-Sutherland Sharks</a>, <a href="/wiki/Wests_Tigers" title="Wests Tigers">Wests Tigers</a>, <a href="/wiki/Penrith_Panthers" title="Penrith Panthers">Penrith Panthers</a>, <a href="/wiki/Canterbury_Bulldogs" title="Canterbury Bulldogs" class="mw-redirect">Canterbury Bulldogs</a> and <a href="/wiki/Manly-Warringah_Sea_Eagles" title="Manly-Warringah Sea Eagles">Manly-Warringah Sea Eagles</a>), as well as being the northern home of the <a href="/wiki/St_George_Illawarra_Dragons" title="St George Illawarra Dragons" class="mw-redirect">St George Illawarra Dragons</a>, which is half-based in <a href="/wiki/Wollongong,_NSW" title="Wollongong, NSW" class="mw-redirect">Wollongong</a>. A tenth team, the <a href="/wiki/Newcastle_Knights" title="Newcastle Knights">Newcastle Knights</a> is located in <a href="/wiki/Newcastle,_New_South_Wales" title="Newcastle, New South Wales">Newcastle</a>. The main summer sport is cricket.</p>

<div class="thumb tleft">
<div class="thumbinner" style="width:172px;"><a href="/wiki/File:Bathurst_Racktrack_Holden_Corner.jpg" class="image"><img alt="" src="http://upload.wikimedia.org/wikipedia/commons/thumb/1/14/Bathurst_Racktrack_Holden_Corner.jpg/170px-Bathurst_Racktrack_Holden_Corner.jpg" width="170" height="128" class="thumbimage" /></a>
<div class="thumbcaption">
<div class="magnify"><a href="/wiki/File:Bathurst_Racktrack_Holden_Corner.jpg" class="internal" title="Enlarge"><img src="http://bits.wikimedia.org/skins-1.5/common/images/magnify-clip.png" width="15" height="11" alt="" /></a></div>
The <a href="/wiki/Bathurst_1000" title="Bathurst 1000">Bathurst 1000</a>, held at <a href="/wiki/Mount_Panorama_Circuit" title="Mount Panorama Circuit">Mount Panorama Circuit</a> in <a href="/wiki/Bathurst,_New_South_Wales" title="Bathurst, New South Wales">Bathurst</a></div>
</div>
</div>
<p>The state is represented by three teams in <a href="/wiki/Football_(soccer)" title="Football (soccer)" class="mw-redirect">football (soccer)</a>\'s <a href="/wiki/A-League" title="A-League">A-League</a>: <a href="/wiki/Sydney_FC" title="Sydney FC">Sydney FC</a> (the inaugural champions in 2005-06), the <a href="/wiki/Central_Coast_Mariners_FC" title="Central Coast Mariners FC">Central Coast Mariners</a>, based at Gosford and the <a href="/wiki/Newcastle_United_Jets" title="Newcastle United Jets" class="mw-redirect">Newcastle United Jets</a> (2007-08 A League Champions). Football has the highest number of registered players in New South Wales of any football code.<sup id="cite_ref-33" class="reference"><a href="#cite_note-33"><span>[</span>34<span>]</span></a></sup> <a href="/wiki/Australian_rules_football" title="Australian rules football">Australian rules football</a> has historically not been strong in New South Wales outside the <a href="/wiki/Riverina" title="Riverina">Riverina</a> region. However, the <a href="/wiki/Sydney_Swans" title="Sydney Swans">Sydney Swans</a> relocated from <a href="/wiki/South_Melbourne,_Victoria" title="South Melbourne, Victoria">South Melbourne</a> in 1982 and their presence and success since the late 1990s has raised the profile of <a href="/wiki/Australian_rules_football" title="Australian rules football">Australian rules football</a>, especially after their AFL premiership in 2005. Other teams in national competitions include basketball\'s <a href="/wiki/Sydney_Spirit" title="Sydney Spirit">Sydney Spirit</a> (formerly the West Sydney Razorbacks) and the defunct team <a href="/wiki/Sydney_Kings" title="Sydney Kings">Sydney Kings</a> and <a href="/w/index.php?title=Sydney_Uni_Flames&amp;action=edit&amp;redlink=1" class="new" title="Sydney Uni Flames (page does not exist)">Sydney Uni Flames</a>, and netball\'s <a href="/wiki/Sydney_Swifts" title="Sydney Swifts">Sydney Swifts</a>.</p>

<p>Sydney was the host of the <a href="/wiki/2000_Summer_Olympics" title="2000 Summer Olympics">2000 Summer Olympics</a> and the <a href="/wiki/1938_British_Empire_Games" title="1938 British Empire Games">1938 British Empire Games</a>. The Olympic Stadium, now known as <a href="/wiki/ANZ_Stadium" title="ANZ Stadium" class="mw-redirect">ANZ Stadium</a> is the scene of the annual NRL Grand Final. It also regularly hosts rugby league State of Origin games and rugby union internationals, and has recently hosted the final of the <a href="/wiki/2003_Rugby_World_Cup" title="2003 Rugby World Cup">2003 Rugby World Cup</a> and the football <a href="/wiki/Football_World_Cup_2006" title="Football World Cup 2006" class="mw-redirect">World Cup</a> <a href="/wiki/Football_World_Cup_2006_-_Oceania-South_America_Qualification_Playoff" title="Football World Cup 2006 - Oceania-South America Qualification Playoff" class="mw-redirect">qualifier</a> between <a href="/wiki/Australia_national_football_team" title="Australia national football team" class="mw-redirect">Australia</a> and <a href="/wiki/Uruguay_national_football_team" title="Uruguay national football team">Uruguay</a>.</p>

<p>The <a href="/wiki/Sydney_Cricket_Ground" title="Sydney Cricket Ground">Sydney Cricket Ground</a> hosts the \'New Year\' cricket <a href="/wiki/Test_cricket" title="Test cricket">Test match</a> from 2–6 January each year, and is also one of the sites for the finals of the <a href="/wiki/One_Day_International" title="One Day International">One Day International</a> series. The annual <a href="/wiki/Sydney_to_Hobart_Yacht_Race" title="Sydney to Hobart Yacht Race">Sydney to Hobart Yacht Race</a> begins in Sydney Harbour on Boxing Day. The climax of Australia\'s <a href="/wiki/Touring_car_racing" title="Touring car racing">touring car racing</a> series is the <a href="/wiki/Bathurst_1000" title="Bathurst 1000">Bathurst 1000</a>, held near the city of <a href="/wiki/Bathurst,_New_South_Wales" title="Bathurst, New South Wales">Bathurst</a>.</p>

<p>The popular equine sports of <a href="/wiki/Campdrafting" title="Campdrafting">campdrafting</a> and <a href="/wiki/Polocrosse" title="Polocrosse">polocrosse</a> were developed in New South Wales and competitions are now held across Australia. Polocrosse is now played in many overseas countries. New South Wales is the home to the world famous <a href="/wiki/Coolmore_Stud" title="Coolmore Stud">Coolmore</a>,<sup id="cite_ref-34" class="reference"><a href="#cite_note-34"><span>[</span>35<span>]</span></a></sup> <a href="/wiki/Darley_Stud" title="Darley Stud">Darley</a> and <a href="/wiki/Kia-Ora_stud" title="Kia-Ora stud">Kia-Ora</a> <a href="/wiki/Thoroughbred" title="Thoroughbred">Thoroughbred</a> horse studs.</p>

'
        );
        $filters = array('1','2','3','4','5','6','7');
        for($i = 21; $i < 32; $i++)
        {
            echo $i;
            $model = new $this->_modelClass();
            $filtercount = rand(0,7);
            $ff = $filters;
            shuffle($ff);
            $fff = array();
            while($filtercount > 0)
            {
                $fff[] = array_pop($ff);
                $filtercount--;
            }

            $data['general'] = array
            (
                'urlId' => 'mockup_'.$i,
                'timestamp' => date('Y-m-d H:i:s', rand()),
                'Filters' => $fff,

            );
            $data['i18n'] = array
            (
                'title' => $titles[array_rand($titles)].$i,
                'teaser' => $teasers[array_rand($teasers)],
                'htmlContent' => $content[array_rand($content)],
                'published' => true,
            );
            $dcmodel = $model->create($data, $langs[array_rand($langs)], 'general', 'i18n');//$post gets changed in here

        }
        exit('ende');
    }
}
