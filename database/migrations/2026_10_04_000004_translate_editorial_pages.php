<?php

use App\Models\PageTranslation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const LOCALES = ['da', 'de', 'el', 'es', 'fr', 'it', 'nl', 'pl', 'pt', 'ro', 'ru', 'sv', 'tr'];

    private function translations(): array
    {
        return [
            'donations' => $this->donations(),
            'how-to-write-a-petition' => $this->howToWriteAPetition(),
        ];
    }

    public function up(): void
    {
        foreach ($this->translations() as $slug => $localeData) {
            $en = DB::table('page_translations')->where('slug', $slug)->where('locale', 'en')->first();
            if (! $en) {
                continue;
            }
            $pageId = $en->page_id;

            DB::transaction(function () use ($pageId, $localeData) {
                foreach ($localeData as $locale => $data) {
                    $exists = PageTranslation::query()
                        ->where('page_id', $pageId)
                        ->where('locale', $locale)
                        ->exists();
                    if ($exists) {
                        continue;
                    }

                    PageTranslation::create([
                        'page_id' => $pageId,
                        'locale' => $locale,
                        'title' => $data['title'],
                        'slug' => $data['slug'],
                        'content' => sanitizeAdminHtml($data['content']),
                        'published' => true,
                    ]);
                }
            });
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->translations()) as $slug) {
            $en = DB::table('page_translations')->where('slug', $slug)->where('locale', 'en')->first();
            if (! $en) {
                continue;
            }

            PageTranslation::query()
                ->where('page_id', $en->page_id)
                ->whereIn('locale', self::LOCALES)
                ->delete();
        }
    }

    private function donations(): array
    {
        return [
            'da' => [
                'title' => 'Donationer',
                'slug' => 'donations',
                'content' => <<<'HTML'
<p><strong>ONLINE-DONATIONER</strong></p>
<h2>FreeCause - Online Petition er et uafhængigt websted. Hjælp venligst med at støtte det med en donation gennem en af de metoder, der er beskrevet på denne side.</h2>
<p><strong>FreeCause - Online Petition</strong> accepterer ikke direkte sponsorering af nogen kampagne og sælger heller ikke navnene eller e-mailadresserne på dem, der underskriver underskriftsindsamlingerne. Det er og vil altid forblive gratis.</p>
<p>Vi ønsker ikke at underkaste os det frie markeds diktater: idealer er uvurderlige og kan ikke købes eller sælges.<br>Af denne grund beder vi om din hjælp.</p>
<p>Mange kampagner, der er startet her på <strong>FreeCause - Online Petition</strong>, er blevet sejrrige og har ændret livet i små samfund eller endda hele lande. Der er intet som den folkelige bevidsthed, internettet kan skabe, som rører samvittigheder og får folk til at handle.</p>
<p>Send os dit bidrag i dag, og vær med til fortsat at tilbyde mennesker over hele verden mulighederne, værktøjerne og den information, der er nødvendig for at skabe forandring i deres samfund.</p>
<p>Det er kun gennem frivillig støtte fra bidragydere som dig, at vi kan fortsætte med at fungere og udvide omfanget af vores projekter.</p>
<p>Alle donationer, vi modtager, bruges udelukkende til vedligeholdelse af <strong>FreeCause - Online Petition</strong>-webstedet over hele verden og til fremme af individuelle underskriftsindsamlinger.</p>
<p>Ved at følge vores etiske kodeks er vi blevet en vigtig partner for nonprofitorganisationer, ikke-statslige organisationer og almindelige borgere i mange lande.<br>Vi er stedet, hvor ikke-voldelig modstand begynder.</p>
<h2>Selv en lille donation hjælper. Støt venligst vores sag</h2>
<h2>Du kan give en donation til FreeCause - Online Petition via:</h2>
<p><strong>ONLINE-DONATIONER</strong></p>
<p>Beløbet kan ændres, som du ønsker og finder passende.</p>
<p><strong>OFFLINE-DONATIONER</strong></p>
<p>Du kan også sende os en check eller foretage en betaling via bankoverførsel.<br>Skriv til os på <a href="mailto:hello@freecause.com">hello@freecause.com</a>, så giver vi dig de nødvendige oplysninger.<br>Vi kan på anmodning levere dokumentation for din donation til skattemæssige formål.</p>
HTML,
            ],
            'de' => [
                'title' => 'Spenden',
                'slug' => 'donations',
                'content' => <<<'HTML'
<p><strong>ONLINE-SPENDEN</strong></p>
<h2>FreeCause - Online Petition ist eine unabhängige Website. Bitte helfen Sie, sie mit einer Spende über eine der auf dieser Seite beschriebenen Methoden zu unterstützen.</h2>
<p><strong>FreeCause - Online Petition</strong> akzeptiert keine direkte Förderung einer Kampagne und verkauft auch nicht die Namen oder E-Mail-Adressen derjenigen, die die Petitionen unterzeichnen. Es ist und bleibt immer kostenlos.</p>
<p>Wir wollen uns nicht den Diktaten des freien Marktes unterwerfen: Ideale sind unbezahlbar und können weder gekauft noch verkauft werden.<br>Aus diesem Grund bitten wir um Ihre Hilfe.</p>
<p>Viele hier bei <strong>FreeCause - Online Petition</strong> gestartete Kampagnen waren siegreich und haben das Leben kleiner Gemeinschaften oder sogar ganzer Länder verändert. Es gibt nichts Vergleichbares zu dem Bewusstsein an der Basis, das das Internet schaffen kann, indem es Gewissen berührt und Menschen zum Handeln bewegt.</p>
<p>Senden Sie uns noch heute Ihren Beitrag und helfen Sie uns, den Menschen auf der ganzen Welt weiterhin die Möglichkeiten, die Werkzeuge und die Informationen zu bieten, die notwendig sind, um Veränderungen in ihren Gemeinschaften zu bewirken.</p>
<p>Nur durch die freiwillige Unterstützung von Beitragenden wie Ihnen können wir weiterarbeiten und den Umfang unserer Projekte erweitern.</p>
<p>Alle Spenden, die wir erhalten, werden ausschließlich für die Pflege der <strong>FreeCause - Online Petition</strong>-Website auf der ganzen Welt und für die Förderung einzelner Petitionen verwendet.</p>
<p>Durch die Befolgung unseres Ethikkodex sind wir zu einem wichtigen Partner für gemeinnützige Organisationen, Nichtregierungsorganisationen und normale Bürger in vielen Ländern geworden.<br>Wir sind der Ort, an dem gewaltfreier Widerstand beginnt.</p>
<h2>Auch eine kleine Spende hilft. Bitte unterstützen Sie unsere Sache</h2>
<h2>Sie können an FreeCause - Online Petition spenden über:</h2>
<p><strong>ONLINE-SPENDEN</strong></p>
<p>Der Betrag kann nach Ihren Wünschen und wie Sie es für angemessen halten geändert werden.</p>
<p><strong>OFFLINE-SPENDEN</strong></p>
<p>Sie können uns auch einen Scheck senden oder eine Zahlung per Banküberweisung vornehmen.<br>Schreiben Sie uns an <a href="mailto:hello@freecause.com">hello@freecause.com</a> und wir stellen Ihnen die notwendigen Angaben zur Verfügung.<br>Auf Anfrage können wir Ihnen eine Dokumentation Ihrer Spende für Steuerzwecke bereitstellen.</p>
HTML,
            ],
            'el' => [
                'title' => 'Δωρεές',
                'slug' => 'donations',
                'content' => <<<'HTML'
<p><strong>ΔΙΑΔΙΚΤΥΑΚΕΣ ΔΩΡΕΕΣ</strong></p>
<h2>Το FreeCause - Online Petition είναι ένας ανεξάρτητος ιστότοπος. Παρακαλούμε βοηθήστε να τον υποστηρίξετε με μια δωρεά μέσω μίας από τις μεθόδους που περιγράφονται σε αυτήν τη σελίδα.</h2>
<p>Το <strong>FreeCause - Online Petition</strong> δεν δέχεται άμεση χορηγία καμίας εκστρατείας, ούτε πουλά τα ονόματα ή τα email εκείνων που υπογράφουν τις αναφορές. Είναι και θα παραμείνει πάντα δωρεάν.</p>
<p>Δεν θέλουμε να υποταχθούμε στις επιταγές της ελεύθερης αγοράς: τα ιδανικά είναι ανεκτίμητα και δεν μπορούν να αγοραστούν ή να πουληθούν.<br>Γι' αυτόν τον λόγο ζητάμε τη βοήθειά σας.</p>
<p>Πολλές εκστρατείες που ξεκίνησαν εδώ στο <strong>FreeCause - Online Petition</strong> έγιναν νικηφόρες και άλλαξαν τις ζωές μικρών κοινοτήτων ή ακόμα και ολόκληρων χωρών. Δεν υπάρχει τίποτα σαν τη λαϊκή συνειδητοποίηση που μπορεί να επιτύχει το Διαδίκτυο, αγγίζοντας συνειδήσεις και κινητοποιώντας τους ανθρώπους σε δράση.</p>
<p>Στείλτε μας τη συνεισφορά σας σήμερα και ελάτε μαζί μας να συνεχίσουμε να προσφέρουμε στους ανθρώπους σε όλο τον κόσμο τη δυνατότητα, τα εργαλεία και τις πληροφορίες που είναι απαραίτητες για τη δημιουργία αλλαγής μέσα στις κοινότητές τους.</p>
<p>Μόνο μέσω της εθελοντικής υποστήριξης από συνεισφέροντες όπως εσείς μπορούμε να συνεχίσουμε να λειτουργούμε και να διευρύνουμε το εύρος των έργων μας.</p>
<p>Όλες οι δωρεές που λαμβάνουμε χρησιμοποιούνται αποκλειστικά για τη συντήρηση του ιστότοπου <strong>FreeCause - Online Petition</strong> σε όλο τον κόσμο και για την προώθηση μεμονωμένων αναφορών.</p>
<p>Ακολουθώντας τον κώδικα δεοντολογίας μας, γίναμε σημαντικός εταίρος για μη κερδοσκοπικούς οργανισμούς, μη κυβερνητικές οργανώσεις και απλούς πολίτες σε πολλές χώρες.<br>Είμαστε το μέρος όπου ξεκινά η μη βίαιη αντίσταση.</p>
<h2>Ακόμα και μια μικρή δωρεά βοηθάει. Παρακαλούμε υποστηρίξτε τον σκοπό μας</h2>
<h2>Μπορείτε να κάνετε μια δωρεά στο FreeCause - Online Petition μέσω:</h2>
<p><strong>ΔΙΑΔΙΚΤΥΑΚΕΣ ΔΩΡΕΕΣ</strong></p>
<p>Το ποσό μπορεί να αλλάξει όπως επιθυμείτε και όπως κρίνετε κατάλληλο.</p>
<p><strong>ΔΩΡΕΕΣ ΕΚΤΟΣ ΔΙΑΔΙΚΤΥΟΥ</strong></p>
<p>Μπορείτε επίσης να μας στείλετε επιταγή ή να κάνετε πληρωμή μέσω τραπεζικού εμβάσματος.<br>Γράψτε μας στο <a href="mailto:hello@freecause.com">hello@freecause.com</a> και θα σας παρέχουμε τις απαραίτητες λεπτομέρειες.<br>Μπορούμε να παρέχουμε, κατόπιν αιτήματος, τεκμηρίωση της δωρεάς σας για φορολογικούς σκοπούς.</p>
HTML,
            ],
            'es' => [
                'title' => 'Donaciones',
                'slug' => 'donations',
                'content' => <<<'HTML'
<p><strong>DONACIONES EN LÍNEA</strong></p>
<h2>FreeCause - Online Petition es un sitio independiente. Por favor, ayuda a apoyarlo con una donación a través de uno de los métodos descritos en esta página.</h2>
<p><strong>FreeCause - Online Petition</strong> no acepta el patrocinio directo de ninguna campaña, ni vende los nombres o correos electrónicos de quienes firman las peticiones. Es y siempre será gratuito.</p>
<p>No queremos someternos a los dictados del libre mercado: los ideales no tienen precio y no se pueden comprar ni vender.<br>Por esta razón pedimos tu ayuda.</p>
<p>Muchas campañas iniciadas aquí en <strong>FreeCause - Online Petition</strong> han resultado victoriosas y han cambiado la vida de pequeñas comunidades o incluso de países enteros. No hay nada como la concienciación de base que Internet puede lograr, tocando conciencias y movilizando a las personas a la acción.</p>
<p>Envíanos tu contribución hoy y únete a nosotros para seguir ofreciendo a las personas de todo el mundo la posibilidad, las herramientas y la información necesarias para generar cambios dentro de sus comunidades.</p>
<p>Solo gracias al apoyo voluntario de colaboradores como tú podemos seguir funcionando y ampliar el alcance de nuestros proyectos.</p>
<p>Todas las donaciones que recibimos se utilizan exclusivamente para el mantenimiento del sitio web <strong>FreeCause - Online Petition</strong> en todo el mundo y para la promoción de peticiones individuales.</p>
<p>Siguiendo nuestro código ético nos hemos convertido en un socio importante para organizaciones sin ánimo de lucro, organizaciones no gubernamentales y ciudadanos comunes en muchos países.<br>Somos el lugar donde comienza la resistencia no violenta.</p>
<h2>Incluso una pequeña donación ayuda. Por favor, apoya nuestra causa</h2>
<h2>Puedes hacer una donación a FreeCause - Online Petition a través de:</h2>
<p><strong>DONACIONES EN LÍNEA</strong></p>
<p>El importe se puede cambiar según desees y consideres apropiado.</p>
<p><strong>DONACIONES FUERA DE LÍNEA</strong></p>
<p>También puedes enviarnos un cheque o realizar un pago por transferencia bancaria.<br>Escríbenos a <a href="mailto:hello@freecause.com">hello@freecause.com</a> y te proporcionaremos los datos necesarios.<br>Podemos proporcionar, previa solicitud, documentación de tu donación a efectos fiscales.</p>
HTML,
            ],
            'fr' => [
                'title' => 'Dons',
                'slug' => 'donations',
                'content' => <<<'HTML'
<p><strong>DONS EN LIGNE</strong></p>
<h2>FreeCause - Online Petition est un site indépendant. Veuillez aider à le soutenir par un don via l'une des méthodes décrites sur cette page.</h2>
<p><strong>FreeCause - Online Petition</strong> n'accepte le parrainage direct d'aucune campagne et ne vend pas non plus les noms ou les e-mails de ceux qui signent les pétitions. Il est et restera toujours gratuit.</p>
<p>Nous ne voulons pas nous soumettre aux diktats du marché libre : les idéaux n'ont pas de prix et ne peuvent être ni achetés ni vendus.<br>Pour cette raison, nous demandons votre aide.</p>
<p>De nombreuses campagnes lancées ici sur <strong>FreeCause - Online Petition</strong> sont devenues victorieuses et ont changé la vie de petites communautés, voire de pays entiers. Rien ne vaut la prise de conscience populaire qu'Internet peut susciter, touchant les consciences et poussant les gens à agir.</p>
<p>Envoyez-nous votre contribution aujourd'hui et rejoignez-nous pour continuer à offrir aux gens du monde entier la possibilité, les outils et les informations nécessaires pour créer le changement au sein de leurs communautés.</p>
<p>Ce n'est que grâce au soutien volontaire de contributeurs comme vous que nous pouvons continuer à fonctionner et à élargir la portée de nos projets.</p>
<p>Tous les dons que nous recevons sont utilisés exclusivement pour la maintenance du site web <strong>FreeCause - Online Petition</strong> à travers le monde et pour la promotion de pétitions individuelles.</p>
<p>En suivant notre code d'éthique, nous sommes devenus un partenaire important pour les organisations à but non lucratif, les organisations non gouvernementales et les citoyens ordinaires dans de nombreux pays.<br>Nous sommes le lieu où commence la résistance non violente.</p>
<h2>Même un petit don aide. Veuillez soutenir notre cause</h2>
<h2>Vous pouvez faire un don à FreeCause - Online Petition via :</h2>
<p><strong>DONS EN LIGNE</strong></p>
<p>Le montant peut être modifié comme vous le souhaitez et le jugez approprié.</p>
<p><strong>DONS HORS LIGNE</strong></p>
<p>Vous pouvez également nous envoyer un chèque ou effectuer un paiement par virement bancaire.<br>Écrivez-nous à <a href="mailto:hello@freecause.com">hello@freecause.com</a> et nous vous fournirons les détails nécessaires.<br>Nous pouvons fournir, sur demande, un justificatif de votre don à des fins fiscales.</p>
HTML,
            ],
            'it' => [
                'title' => 'Donazioni',
                'slug' => 'donations',
                'content' => <<<'HTML'
<p><strong>DONAZIONI ONLINE</strong></p>
<h2>FreeCause - Online Petition è un sito indipendente. Ti preghiamo di aiutare a sostenerlo con una donazione tramite uno dei metodi descritti in questa pagina.</h2>
<p><strong>FreeCause - Online Petition</strong> non accetta la sponsorizzazione diretta di alcuna campagna, né vende i nomi o le email di coloro che firmano le petizioni. È e rimarrà sempre gratuito.</p>
<p>Non vogliamo sottometterci ai dettami del libero mercato: gli ideali non hanno prezzo e non possono essere comprati o venduti.<br>Per questo motivo chiediamo il tuo aiuto.</p>
<p>Molte campagne avviate qui su <strong>FreeCause - Online Petition</strong> sono diventate vittoriose e hanno cambiato la vita di piccole comunità o persino di interi paesi. Non c'è niente come la consapevolezza dal basso che Internet può raggiungere, toccando le coscienze e spingendo le persone all'azione.</p>
<p>Inviaci il tuo contributo oggi e unisciti a noi per continuare a offrire alle persone di tutto il mondo la possibilità, gli strumenti e le informazioni necessarie per creare cambiamento all'interno delle loro comunità.</p>
<p>È solo grazie al sostegno volontario di contributori come te che possiamo continuare a funzionare e ad ampliare la portata dei nostri progetti.</p>
<p>Tutte le donazioni che riceviamo sono utilizzate esclusivamente per la manutenzione del sito web <strong>FreeCause - Online Petition</strong> in tutto il mondo e per la promozione di singole petizioni.</p>
<p>Seguendo il nostro codice etico siamo diventati un partner importante per organizzazioni senza scopo di lucro, organizzazioni non governative e cittadini comuni in molti paesi.<br>Siamo il luogo dove inizia la resistenza non violenta.</p>
<h2>Anche una piccola donazione aiuta. Ti preghiamo di sostenere la nostra causa</h2>
<h2>Puoi fare una donazione a FreeCause - Online Petition tramite:</h2>
<p><strong>DONAZIONI ONLINE</strong></p>
<p>L'importo può essere modificato come desideri e ritieni appropriato.</p>
<p><strong>DONAZIONI OFFLINE</strong></p>
<p>Puoi anche inviarci un assegno o effettuare un pagamento tramite bonifico bancario.<br>Scrivici a <a href="mailto:hello@freecause.com">hello@freecause.com</a> e ti forniremo i dettagli necessari.<br>Possiamo fornire, su richiesta, la documentazione della tua donazione a fini fiscali.</p>
HTML,
            ],
            'nl' => [
                'title' => 'Donaties',
                'slug' => 'donations',
                'content' => <<<'HTML'
<p><strong>ONLINE DONATIES</strong></p>
<h2>FreeCause - Online Petition is een onafhankelijke site. Help deze alstublieft te ondersteunen met een donatie via een van de methoden die op deze pagina worden beschreven.</h2>
<p><strong>FreeCause - Online Petition</strong> accepteert geen directe sponsoring van welke campagne dan ook en verkoopt ook niet de namen of e-mailadressen van degenen die de petities ondertekenen. Het is en blijft altijd gratis.</p>
<p>Wij willen ons niet onderwerpen aan de dictaten van de vrije markt: idealen zijn onbetaalbaar en kunnen niet worden gekocht of verkocht.<br>Om deze reden vragen wij uw hulp.</p>
<p>Veel campagnes die hier op <strong>FreeCause - Online Petition</strong> zijn gestart, zijn zegevierend geworden en hebben het leven van kleine gemeenschappen of zelfs hele landen veranderd. Er is niets zoals het bewustzijn vanuit de basis dat het internet kan bereiken, dat het geweten raakt en mensen tot actie beweegt.</p>
<p>Stuur ons vandaag uw bijdrage en help ons mensen over de hele wereld te blijven bieden wat zij nodig hebben: de mogelijkheid, de hulpmiddelen en de informatie om verandering teweeg te brengen binnen hun gemeenschappen.</p>
<p>Alleen dankzij de vrijwillige steun van bijdragers zoals u kunnen wij blijven functioneren en de reikwijdte van onze projecten uitbreiden.</p>
<p>Alle donaties die wij ontvangen, worden uitsluitend gebruikt voor het onderhoud van de <strong>FreeCause - Online Petition</strong>-website over de hele wereld en voor de promotie van individuele petities.</p>
<p>Door onze ethische code te volgen, zijn wij een belangrijke partner geworden voor non-profitorganisaties, niet-gouvernementele organisaties en gewone burgers in veel landen.<br>Wij zijn de plek waar geweldloos verzet begint.</p>
<h2>Zelfs een kleine donatie helpt. Steun alstublieft onze zaak</h2>
<h2>U kunt een donatie doen aan FreeCause - Online Petition via:</h2>
<p><strong>ONLINE DONATIES</strong></p>
<p>Het bedrag kan worden gewijzigd zoals u wenst en passend acht.</p>
<p><strong>OFFLINE DONATIES</strong></p>
<p>U kunt ons ook een cheque sturen of een betaling doen via bankoverschrijving.<br>Schrijf ons op <a href="mailto:hello@freecause.com">hello@freecause.com</a> en wij verstrekken u de nodige gegevens.<br>Wij kunnen op verzoek documentatie van uw donatie voor belastingdoeleinden verstrekken.</p>
HTML,
            ],
            'pl' => [
                'title' => 'Darowizny',
                'slug' => 'donations',
                'content' => <<<'HTML'
<p><strong>DAROWIZNY ONLINE</strong></p>
<h2>FreeCause - Online Petition to niezależna strona. Prosimy o wsparcie jej darowizną za pomocą jednej z metod opisanych na tej stronie.</h2>
<p><strong>FreeCause - Online Petition</strong> nie przyjmuje bezpośredniego sponsorowania żadnej kampanii, ani nie sprzedaje nazwisk ani adresów e-mail osób podpisujących petycje. Jest i zawsze pozostanie bezpłatna.</p>
<p>Nie chcemy poddawać się dyktatowi wolnego rynku: ideały są bezcenne i nie można ich kupić ani sprzedać.<br>Z tego powodu prosimy o Twoją pomoc.</p>
<p>Wiele kampanii rozpoczętych tutaj na <strong>FreeCause - Online Petition</strong> zakończyło się sukcesem i zmieniło życie małych społeczności, a nawet całych krajów. Nie ma nic podobnego do oddolnej świadomości, jaką może osiągnąć internet, poruszając sumienia i pobudzając ludzi do działania.</p>
<p>Prześlij nam swój wkład już dziś i dołącz do nas, aby nadal oferować ludziom na całym świecie możliwości, narzędzia i informacje niezbędne do tworzenia zmian w ich społecznościach.</p>
<p>Tylko dzięki dobrowolnemu wsparciu darczyńców takich jak Ty możemy nadal funkcjonować i rozszerzać zakres naszych projektów.</p>
<p>Wszystkie otrzymywane przez nas darowizny są wykorzystywane wyłącznie na utrzymanie witryny <strong>FreeCause - Online Petition</strong> na całym świecie oraz na promocję poszczególnych petycji.</p>
<p>Przestrzegając naszego kodeksu etycznego, staliśmy się ważnym partnerem dla organizacji non-profit, organizacji pozarządowych i zwykłych obywateli w wielu krajach.<br>Jesteśmy miejscem, w którym zaczyna się opór bez przemocy.</p>
<h2>Nawet mała darowizna pomaga. Prosimy wesprzeć naszą sprawę</h2>
<h2>Możesz przekazać darowiznę na rzecz FreeCause - Online Petition poprzez:</h2>
<p><strong>DAROWIZNY ONLINE</strong></p>
<p>Kwotę można zmienić zgodnie z własnym życzeniem i uznaniem.</p>
<p><strong>DAROWIZNY OFFLINE</strong></p>
<p>Możesz również wysłać nam czek lub dokonać płatności przelewem bankowym.<br>Napisz do nas na adres <a href="mailto:hello@freecause.com">hello@freecause.com</a>, a my prześlemy niezbędne szczegóły.<br>Na życzenie możemy dostarczyć dokumentację Twojej darowizny do celów podatkowych.</p>
HTML,
            ],
            'pt' => [
                'title' => 'Doações',
                'slug' => 'donations',
                'content' => <<<'HTML'
<p><strong>DOAÇÕES ONLINE</strong></p>
<h2>FreeCause - Online Petition é um site independente. Por favor, ajude a apoiá-lo com uma doação através de um dos métodos descritos nesta página.</h2>
<p>O <strong>FreeCause - Online Petition</strong> não aceita o patrocínio direto de qualquer campanha, nem vende os nomes ou e-mails daqueles que assinam as petições. É e sempre será gratuito.</p>
<p>Não queremos submeter-nos aos ditames do mercado livre: os ideais não têm preço e não podem ser comprados ou vendidos.<br>Por esta razão, pedimos a sua ajuda.</p>
<p>Muitas campanhas iniciadas aqui no <strong>FreeCause - Online Petition</strong> tornaram-se vitoriosas e mudaram a vida de pequenas comunidades ou mesmo de países inteiros. Não há nada como a consciencialização de base que a Internet pode alcançar, tocando consciências e movendo as pessoas à ação.</p>
<p>Envie-nos a sua contribuição hoje e junte-se a nós para continuar a oferecer às pessoas de todo o mundo a possibilidade, as ferramentas e as informações necessárias para criar mudança dentro das suas comunidades.</p>
<p>É apenas através do apoio voluntário de contribuidores como você que podemos continuar a funcionar e a expandir o alcance dos nossos projetos.</p>
<p>Todas as doações que recebemos são utilizadas exclusivamente para a manutenção do site <strong>FreeCause - Online Petition</strong> em todo o mundo e para a promoção de petições individuais.</p>
<p>Ao seguir o nosso código de ética, tornámo-nos um parceiro importante para organizações sem fins lucrativos, organizações não governamentais e cidadãos comuns em muitos países.<br>Somos o lugar onde começa a resistência não violenta.</p>
<h2>Mesmo uma pequena doação ajuda. Por favor, apoie a nossa causa</h2>
<h2>Pode fazer uma doação ao FreeCause - Online Petition através de:</h2>
<p><strong>DOAÇÕES ONLINE</strong></p>
<p>O valor pode ser alterado conforme desejar e achar apropriado.</p>
<p><strong>DOAÇÕES OFFLINE</strong></p>
<p>Também pode enviar-nos um cheque ou efetuar um pagamento por transferência bancária.<br>Escreva-nos para <a href="mailto:hello@freecause.com">hello@freecause.com</a> e forneceremos os detalhes necessários.<br>Podemos fornecer, mediante pedido, a documentação da sua doação para fins fiscais.</p>
HTML,
            ],
            'ro' => [
                'title' => 'Donații',
                'slug' => 'donations',
                'content' => <<<'HTML'
<p><strong>DONAȚII ONLINE</strong></p>
<h2>FreeCause - Online Petition este un site independent. Vă rugăm să ajutați la susținerea lui cu o donație prin una dintre metodele descrise pe această pagină.</h2>
<p><strong>FreeCause - Online Petition</strong> nu acceptă sponsorizarea directă a vreunei campanii și nici nu vinde numele sau adresele de e-mail ale celor care semnează petițiile. Este și va rămâne întotdeauna gratuit.</p>
<p>Nu dorim să ne supunem dictaturii pieței libere: idealurile sunt neprețuite și nu pot fi cumpărate sau vândute.<br>Din acest motiv vă cerem ajutorul.</p>
<p>Multe campanii începute aici pe <strong>FreeCause - Online Petition</strong> au devenit victorioase și au schimbat viețile unor mici comunități sau chiar ale unor țări întregi. Nu există nimic asemănător cu conștientizarea de la bază pe care o poate realiza internetul, atingând conștiințe și determinând oamenii să acționeze.</p>
<p>Trimiteți-ne contribuția dumneavoastră astăzi și alăturați-vă nouă pentru a continua să oferim oamenilor din întreaga lume posibilitatea, instrumentele și informațiile necesare pentru a crea schimbare în cadrul comunităților lor.</p>
<p>Doar prin sprijinul voluntar al contribuitorilor ca dumneavoastră putem continua să funcționăm și să extindem domeniul proiectelor noastre.</p>
<p>Toate donațiile pe care le primim sunt utilizate exclusiv pentru întreținerea site-ului <strong>FreeCause - Online Petition</strong> în întreaga lume și pentru promovarea petițiilor individuale.</p>
<p>Urmând codul nostru etic, am devenit un partener important pentru organizațiile non-profit, organizațiile neguvernamentale și cetățenii obișnuiți din multe țări.<br>Suntem locul unde începe rezistența non-violentă.</p>
<h2>Chiar și o mică donație ajută. Vă rugăm să susțineți cauza noastră</h2>
<h2>Puteți face o donație către FreeCause - Online Petition prin:</h2>
<p><strong>DONAȚII ONLINE</strong></p>
<p>Suma poate fi modificată după dorința și aprecierea dumneavoastră.</p>
<p><strong>DONAȚII OFFLINE</strong></p>
<p>De asemenea, ne puteți trimite un cec sau puteți efectua o plată prin transfer bancar.<br>Scrieți-ne la <a href="mailto:hello@freecause.com">hello@freecause.com</a> și vă vom furniza detaliile necesare.<br>Putem furniza, la cerere, documentația donației dumneavoastră în scopuri fiscale.</p>
HTML,
            ],
            'ru' => [
                'title' => 'Пожертвования',
                'slug' => 'donations',
                'content' => <<<'HTML'
<p><strong>ОНЛАЙН-ПОЖЕРТВОВАНИЯ</strong></p>
<h2>FreeCause - Online Petition — это независимый сайт. Пожалуйста, помогите поддержать его пожертвованием одним из способов, описанных на этой странице.</h2>
<p><strong>FreeCause - Online Petition</strong> не принимает прямого спонсорства какой-либо кампании и не продаёт имена или адреса электронной почты тех, кто подписывает петиции. Он является и всегда будет оставаться бесплатным.</p>
<p>Мы не хотим подчиняться диктату свободного рынка: идеалы бесценны, их нельзя купить или продать.<br>По этой причине мы просим вашей помощи.</p>
<p>Многие кампании, начатые здесь, на <strong>FreeCause - Online Petition</strong>, стали победоносными и изменили жизнь небольших сообществ или даже целых стран. Нет ничего подобного низовой осведомлённости, которую может обеспечить интернет, затрагивая совесть и побуждая людей к действию.</p>
<p>Пришлите нам свой вклад сегодня и присоединяйтесь к нам, чтобы продолжать предоставлять людям во всём мире возможности, инструменты и информацию, необходимые для создания перемен в их сообществах.</p>
<p>Только благодаря добровольной поддержке таких жертвователей, как вы, мы можем продолжать работать и расширять охват наших проектов.</p>
<p>Все получаемые нами пожертвования используются исключительно для поддержки сайта <strong>FreeCause - Online Petition</strong> по всему миру и для продвижения отдельных петиций.</p>
<p>Следуя нашему этическому кодексу, мы стали важным партнёром для некоммерческих организаций, неправительственных организаций и обычных граждан во многих странах.<br>Мы — место, где начинается ненасильственное сопротивление.</p>
<h2>Даже небольшое пожертвование помогает. Пожалуйста, поддержите наше дело</h2>
<h2>Вы можете сделать пожертвование FreeCause - Online Petition через:</h2>
<p><strong>ОНЛАЙН-ПОЖЕРТВОВАНИЯ</strong></p>
<p>Сумму можно изменить по вашему желанию и усмотрению.</p>
<p><strong>ОФЛАЙН-ПОЖЕРТВОВАНИЯ</strong></p>
<p>Вы также можете отправить нам чек или произвести оплату банковским переводом.<br>Напишите нам на <a href="mailto:hello@freecause.com">hello@freecause.com</a>, и мы предоставим необходимые реквизиты.<br>По запросу мы можем предоставить документацию о вашем пожертвовании для налоговых целей.</p>
HTML,
            ],
            'sv' => [
                'title' => 'Donationer',
                'slug' => 'donations',
                'content' => <<<'HTML'
<p><strong>DONATIONER ONLINE</strong></p>
<h2>FreeCause - Online Petition är en oberoende webbplats. Hjälp gärna till att stödja den med en donation via en av metoderna som beskrivs på denna sida.</h2>
<p><strong>FreeCause - Online Petition</strong> accepterar inte direkt sponsring av någon kampanj och säljer inte heller namnen eller e-postadresserna till dem som skriver under namninsamlingarna. Den är och kommer alltid att förbli gratis.</p>
<p>Vi vill inte underkasta oss den fria marknadens diktat: ideal är ovärderliga och kan inte köpas eller säljas.<br>Av denna anledning ber vi om din hjälp.</p>
<p>Många kampanjer som startats här på <strong>FreeCause - Online Petition</strong> har blivit segerrika och har förändrat livet för små samhällen eller till och med hela länder. Det finns inget som den gräsrotsmedvetenhet som internet kan åstadkomma, som berör samveten och får människor att agera.</p>
<p>Skicka oss ditt bidrag idag och var med oss och fortsätt att erbjuda människor världen över möjligheten, verktygen och den information som behövs för att skapa förändring inom sina samhällen.</p>
<p>Det är endast genom frivilligt stöd från bidragsgivare som du som vi kan fortsätta att fungera och utöka omfattningen av våra projekt.</p>
<p>Alla donationer vi tar emot används uteslutande för underhåll av webbplatsen <strong>FreeCause - Online Petition</strong> över hela världen och för att främja enskilda namninsamlingar.</p>
<p>Genom att följa vår etiska kod har vi blivit en viktig partner för ideella organisationer, icke-statliga organisationer och vanliga medborgare i många länder.<br>Vi är platsen där ickevåldsamt motstånd börjar.</p>
<h2>Även en liten donation hjälper. Stöd gärna vår sak</h2>
<h2>Du kan göra en donation till FreeCause - Online Petition via:</h2>
<p><strong>DONATIONER ONLINE</strong></p>
<p>Beloppet kan ändras som du önskar och finner lämpligt.</p>
<p><strong>DONATIONER OFFLINE</strong></p>
<p>Du kan också skicka oss en check eller göra en betalning via banköverföring.<br>Skriv till oss på <a href="mailto:hello@freecause.com">hello@freecause.com</a> så lämnar vi de nödvändiga uppgifterna.<br>Vi kan på begäran tillhandahålla dokumentation av din donation för skatteändamål.</p>
HTML,
            ],
            'tr' => [
                'title' => 'Bağışlar',
                'slug' => 'donations',
                'content' => <<<'HTML'
<p><strong>ÇEVRİMİÇİ BAĞIŞLAR</strong></p>
<h2>FreeCause - Online Petition bağımsız bir sitedir. Lütfen bu sayfada açıklanan yöntemlerden biriyle bir bağış yaparak desteklenmesine yardımcı olun.</h2>
<p><strong>FreeCause - Online Petition</strong> herhangi bir kampanyanın doğrudan sponsorluğunu kabul etmez ve dilekçeleri imzalayanların adlarını veya e-postalarını satmaz. Ücretsizdir ve her zaman ücretsiz kalacaktır.</p>
<p>Serbest piyasanın dayatmalarına boyun eğmek istemiyoruz: idealler paha biçilemez ve satın alınamaz veya satılamaz.<br>Bu nedenle yardımınızı istiyoruz.</p>
<p><strong>FreeCause - Online Petition</strong> üzerinde burada başlatılan birçok kampanya zafere ulaştı ve küçük toplulukların, hatta tüm ülkelerin yaşamlarını değiştirdi. İnternetin ulaşabileceği, vicdanlara dokunan ve insanları eyleme geçiren tabandan gelen farkındalık gibisi yoktur.</p>
<p>Bugün bize katkınızı gönderin ve dünyanın dört bir yanındaki insanlara topluluklarında değişim yaratmak için gereken olanağı, araçları ve bilgileri sunmaya devam etmek üzere bize katılın.</p>
<p>Yalnızca sizin gibi katkıda bulunanların gönüllü desteği sayesinde çalışmaya ve projelerimizin kapsamını genişletmeye devam edebiliriz.</p>
<p>Aldığımız tüm bağışlar yalnızca dünya genelinde <strong>FreeCause - Online Petition</strong> web sitesinin bakımı ve bireysel dilekçelerin tanıtımı için kullanılır.</p>
<p>Etik kurallarımızı izleyerek birçok ülkede kâr amacı gütmeyen kuruluşlar, sivil toplum kuruluşları ve sıradan vatandaşlar için önemli bir ortak haline geldik.<br>Biz, şiddet içermeyen direnişin başladığı yeriz.</p>
<h2>Küçük bir bağış bile yardımcı olur. Lütfen davamıza destek olun</h2>
<h2>FreeCause - Online Petition'a şu yollarla bağış yapabilirsiniz:</h2>
<p><strong>ÇEVRİMİÇİ BAĞIŞLAR</strong></p>
<p>Tutar, istediğiniz ve uygun gördüğünüz şekilde değiştirilebilir.</p>
<p><strong>ÇEVRİMDIŞI BAĞIŞLAR</strong></p>
<p>Ayrıca bize bir çek gönderebilir veya banka havalesi yoluyla ödeme yapabilirsiniz.<br>Bize <a href="mailto:hello@freecause.com">hello@freecause.com</a> adresinden yazın, gerekli ayrıntıları sağlayalım.<br>Talep üzerine, vergi amaçlı olarak bağışınızın belgelerini sağlayabiliriz.</p>
HTML,
            ],
        ];
    }

    private function howToWriteAPetition(): array
    {
        return [
            'da' => [
                'title' => 'Sådan skriver du en underskriftsindsamling',
                'slug' => 'how-to-write-a-petition',
                'content' => <<<'HTML'
<h2>Introduktion</h2>
<p>Hver af os, dem der af den ene eller anden grund føler sig tvunget til at protestere mod noget og ønsker at handle. Nogle ønsker, at deres politikere skal træde tilbage fra deres rolle, mens andre ønsker at udtrykke deres støtte til en politiker, og nogle ønsker, at deres yndlings-tv-program kommer på skærmen igen, og atter andre ønsker at se deres yndlingsbands musik i deres hjemby.</p>
<p>Mange af os tror også på noget og har værdier. <em>Online Petition</em> sørger for, at disse værdier ikke krænkes, og at rettighederne ikke krænkes.<br>Naturen, økonomien og politikken og menneskerettighederne er i centrum for vores opmærksomhed.</p>
<p>At skrive en underskriftsindsamling er ikke så svært, som du måske tror.</p>
<p>Mange aktivister har vundet deres kamp med <em>Online Petition</em>, det vil sige ved at bruge vores platform, som er enkel og kraftfuld. Det er svært at sige præcist, hvad der gør, at en underskriftsindsamling underskrives af mange mennesker og når resultatet. I nogle tilfælde er det bare held. I andre er det timing. Det er dog muligt at udlede nogle generelle principper og retningslinjer for at skrive en god underskriftsindsamling.</p>
<h2>Retningslinjer for at skrive en god underskriftsindsamling</h2>
<p>Her er nogle af de bedste tips fra <em>Online Petition</em>-teamet:</p>
<ul>
<li><p>Skriv din underskriftsindsamling klart og kortfattet. Mange af dine potentielle underskrivere har ofte ikke meget tid til rådighed. Det er vigtigt, at hovedmålet præsenteres kortfattet i den indledende del af underskriftsindsamlingen. I det første afsnit skal du angive de mål, som underskriftsindsamlingen sigter mod at opnå.</p></li>
<li><p>Stavekontrol<br>På mange sider bekymrer du dig slet ikke om at lave stavekontrol af den underskriftsindsamling, der blev lagt online. Hvis du ønsker, at folk tager din underskriftsindsamling alvorligt, skal du bruge lidt af din tid på at sikre, at formularen er korrekt redigeret og især præsenteret for offentligheden på en seriøs og professionel måde.</p></li>
<li><p>Brug "internettet" til at udbrede den.<br>Reklamer for og promover din underskriftsindsamling ved hjælp af e-mail og sociale netværk som Facebook og Twitter. Du vil blive overrasket over de sociale netværks evne til at udbrede og udbrede budskabet. Bed også alle dine kontakter om at fortælle deres venner om din underskriftsindsamling. Denne form for reklame kaldet "viral markedsføring" er den mest effektive måde at reklamere for et emne på nettet.</p></li>
<li><p>Prøv at være respektfuld over for andre!<br>Det er vigtigt, at du klart udtrykker målene i teksten til underskriftsindsamlingen. Prøv at gøre det på en måde, der respekterer andres ideer. Mange potentielle underskrivere er, selvom de støtter målene, ofte irriterede over underskriftsindsamlinger, der er stødende i deres indhold.</p></li>
<li><p>Angiv dine mål<br>Angiv præcist, hvad målene for din underskriftsindsamling er, og til hvilket formål du vil bruge underskriftsindsamlingen. Angiv, hvem underskriftsindsamlingen er rettet mod.</p></li>
<li><p>Offline-promovering af en underskriftsindsamling<br>Det faktum, at du har oprettet en online underskriftsindsamling, betyder ikke, at du kun skal promovere den på websteder. Du kan også promovere din underskriftsindsamling i dit daglige liv. Der er mange måder at promovere den på derhjemme, på arbejdet og mange andre steder, hvor du kan indsamle flere underskrifter. For eksempel gennem plakater i din bys biblioteker, eller endda ved at lave promovering på arbejdet og gøre folk opmærksomme på, at du har oprettet en kampagne om et bestemt emne.</p></li>
</ul>
HTML,
            ],
            'de' => [
                'title' => 'Wie man eine Petition schreibt',
                'slug' => 'how-to-write-a-petition',
                'content' => <<<'HTML'
<h2>Einführung</h2>
<p>Jeder von uns, diejenigen, die sich aus dem einen oder anderen Grund gezwungen fühlen, gegen etwas zu protestieren und handeln wollen. Einige möchten, dass ihre Politiker von ihrem Amt zurücktreten, während andere ihre Unterstützung für einen Politiker ausdrücken möchten, und manche wünschen sich, dass ihre Lieblingsfernsehsendung wieder ausgestrahlt wird, und wieder andere möchten die Musik ihrer Lieblingsband in ihrer Heimatstadt sehen.</p>
<p>Viele von uns glauben auch an etwas und haben Werte. <em>Online Petition</em> achtet darauf, dass diese Werte nicht verletzt werden und die Rechte nicht verletzt werden.<br>Die Natur, die Wirtschaft und die Politik und die Menschenrechte stehen im Mittelpunkt unserer Aufmerksamkeit.</p>
<p>Eine Petition zu schreiben ist nicht so schwierig, wie man denken könnte.</p>
<p>Viele Aktivisten haben ihren Kampf mit <em>Online Petition</em> gewonnen, das heißt mithilfe unserer Plattform, die einfach und leistungsstark ist. Es ist schwer, genau zu sagen, was dazu führt, dass eine Petition von vielen Menschen unterschrieben wird und das Ergebnis erreicht. In manchen Fällen ist es einfach Glück. In anderen ist es der richtige Zeitpunkt. Es ist jedoch möglich, einige allgemeine Grundsätze und Richtlinien für das Schreiben einer guten Petition abzuleiten.</p>
<h2>Richtlinien für das Schreiben einer guten Petition</h2>
<p>Hier sind einige der besten Tipps vom Team von <em>Online Petition</em>:</p>
<ul>
<li><p>Schreiben Sie Ihre Petition klar und prägnant. Viele der potenziellen Unterzeichner Ihrer Petition haben oft nicht viel Zeit zur Verfügung. Es ist wichtig, dass im einleitenden Teil der Petition das Hauptziel prägnant dargestellt wird. Im ersten Absatz müssen Sie die Ziele angeben, die die Petition erreichen soll.</p></li>
<li><p>Rechtschreibprüfung<br>Auf vielen Seiten machen Sie sich überhaupt keine Sorgen, eine Rechtschreibprüfung der online gestellten Petition durchzuführen. Wenn Sie möchten, dass die Menschen Ihre Petition ernst nehmen, müssen Sie ein wenig Ihrer Zeit aufwenden, um sicherzustellen, dass das Formular korrekt bearbeitet und vor allem der Öffentlichkeit auf seriöse und professionelle Weise präsentiert wird.</p></li>
<li><p>Nutzen Sie das "Internet", um sie zu verbreiten.<br>Werben Sie für Ihre Petition und bewerben Sie sie über E-Mail und soziale Netzwerke wie Facebook und Twitter. Sie werden von der Fähigkeit der sozialen Netzwerke überrascht sein, Inhalte zu verbreiten und bekannt zu machen. Bitten Sie auch alle Ihre Kontakte, ihren Freunden von Ihrer Petition zu erzählen. Diese Form der Werbung, die als "virales Marketing" bezeichnet wird, ist der effektivste Weg, ein Thema im Web zu bewerben.</p></li>
<li><p>Versuchen Sie, andere zu respektieren!<br>Es ist wichtig, dass Sie die Ziele im Text der Petition klar zum Ausdruck bringen. Versuchen Sie, dies auf eine Weise zu tun, die die Ideen anderer respektiert. Viele potenzielle Unterzeichner sind, obwohl sie die Ziele unterstützen, oft von Petitionen genervt, die in ihrem Inhalt beleidigend sind.</p></li>
<li><p>Geben Sie Ihre Ziele an<br>Geben Sie genau an, was die Ziele Ihrer Petition sind und zu welchem Zweck Sie die Petition verwenden werden. Geben Sie an, an wen die Petition gerichtet ist.</p></li>
<li><p>Offline-Werbung für eine Petition<br>Die Tatsache, dass Sie eine Online-Petition erstellt haben, bedeutet nicht, dass Sie sie nur auf Websites bewerben. Sie können Ihre Petition auch in Ihrem täglichen Leben bewerben. Es gibt viele Möglichkeiten, sie zu Hause, bei der Arbeit und an vielen anderen Orten zu bewerben, wo Sie mehr Unterschriften sammeln können. Zum Beispiel durch Plakate in den Bibliotheken Ihrer Stadt oder sogar durch Werbung bei der Arbeit und indem Sie die Menschen darauf aufmerksam machen, dass Sie eine Aktion zu einem bestimmten Thema erstellt haben.</p></li>
</ul>
HTML,
            ],
            'el' => [
                'title' => 'Πώς να γράψετε μια αναφορά',
                'slug' => 'how-to-write-a-petition',
                'content' => <<<'HTML'
<h2>Εισαγωγή</h2>
<p>Ο καθένας μας, εκείνοι που για τον έναν ή τον άλλον λόγο αισθάνονται την ανάγκη να διαμαρτυρηθούν για κάτι και θέλουν να δράσουν. Κάποιοι θέλουν οι πολιτικοί τους να παραιτηθούν από τον ρόλο τους, ενώ άλλοι θέλουν να εκφράσουν την υποστήριξή τους σε έναν πολιτικό, και κάποιοι θέλουν το αγαπημένο τους τηλεοπτικό πρόγραμμα να προβληθεί ξανά, ενώ άλλοι θέλουν να δουν τη μουσική του αγαπημένου τους συγκροτήματος στην πόλη τους.</p>
<p>Πολλοί από εμάς πιστεύουμε επίσης σε κάτι και έχουμε αξίες. Το <em>Online Petition</em> φροντίζει να διασφαλίσει ότι αυτές οι αξίες δεν παραβιάζονται και ότι τα δικαιώματα δεν παραβιάζονται.<br>Η φύση, η οικονομία και η πολιτική και τα ανθρώπινα δικαιώματα βρίσκονται στο επίκεντρο της προσοχής μας.</p>
<p>Το να γράψετε μια αναφορά δεν είναι τόσο δύσκολο όσο νομίζετε.</p>
<p>Πολλοί ακτιβιστές έχουν κερδίσει τη μάχη τους με το <em>Online Petition</em>, δηλαδή χρησιμοποιώντας την πλατφόρμα μας, η οποία είναι απλή και ισχυρή. Είναι δύσκολο να πει κανείς με ακρίβεια τι κάνει μια αναφορά να υπογραφεί από πολλούς ανθρώπους και να φτάσει στο αποτέλεσμα. Σε ορισμένες περιπτώσεις είναι απλώς τύχη. Σε άλλες είναι ο χρόνος. Ωστόσο, είναι δυνατόν να εξαχθούν ορισμένες γενικές αρχές και κατευθυντήριες γραμμές για τη συγγραφή μιας καλής αναφοράς.</p>
<h2>Κατευθυντήριες γραμμές για τη συγγραφή μιας καλής αναφοράς</h2>
<p>Ακολουθούν μερικές από τις καλύτερες συμβουλές από την ομάδα του <em>Online Petition</em>:</p>
<ul>
<li><p>Γράψτε την αναφορά σας με σαφήνεια και συντομία. Πολλοί από τους πιθανούς υπογράφοντες της αναφοράς σας συχνά δεν έχουν πολύ χρόνο στη διάθεσή τους. Είναι σημαντικό στο εισαγωγικό μέρος της αναφοράς να παρουσιάζεται με συνοπτικό τρόπο ο κύριος στόχος. Στην πρώτη παράγραφο πρέπει να προσδιορίσετε τους στόχους που επιδιώκει να επιτύχει η αναφορά.</p></li>
<li><p>Ορθογραφικός έλεγχος<br>Σε πολλούς ιστότοπους δεν ανησυχείτε καθόλου για τον ορθογραφικό έλεγχο της αναφοράς που δημοσιεύτηκε στο διαδίκτυο. Αν θέλετε οι άνθρωποι να πάρουν στα σοβαρά την αναφορά σας, πρέπει να αφιερώσετε λίγο από τον χρόνο σας για να διασφαλίσετε ότι η φόρμα είναι σωστά επεξεργασμένη και κυρίως ότι προτείνεται στο κοινό με σοβαρό και επαγγελματικό τρόπο.</p></li>
<li><p>Χρησιμοποιήστε το "Διαδίκτυο" για να τη διαδώσετε.<br>Διαφημίστε και προωθήστε την αναφορά σας χρησιμοποιώντας email και ιστότοπους κοινωνικής δικτύωσης όπως το Facebook και το Twitter. Θα εκπλαγείτε από την ικανότητα διάδοσης και δημοσιοποίησης των κοινωνικών δικτύων. Ζητήστε επίσης από όλες τις επαφές σας να μιλήσουν στους φίλους τους για την αναφορά σας. Αυτή η μορφή διαφήμισης που ονομάζεται "ιογενές μάρκετινγκ" είναι ο πιο αποτελεσματικός τρόπος για να διαφημίσετε ένα θέμα στον ιστό.</p></li>
<li><p>Προσπαθήστε να σέβεστε τους άλλους!<br>Είναι σημαντικό να εκφράσετε με σαφήνεια τους στόχους στο κείμενο της αναφοράς. Προσπαθήστε να το κάνετε με τρόπο που σέβεται τις ιδέες των άλλων. Πολλοί πιθανοί υπογράφοντες, ενώ υποστηρίζουν τους στόχους, συχνά ενοχλούνται από αναφορές που είναι προσβλητικές στο περιεχόμενό τους.</p></li>
<li><p>Προσδιορίστε τους στόχους σας<br>Υποδείξτε με ακρίβεια ποιοι είναι οι στόχοι της αναφοράς σας και για ποιον σκοπό θα χρησιμοποιήσετε την αναφορά. Υποδείξτε σε ποιον απευθύνεται η αναφορά.</p></li>
<li><p>Προώθηση μιας αναφοράς εκτός διαδικτύου<br>Το γεγονός ότι έχετε δημιουργήσει μια διαδικτυακή αναφορά δεν σημαίνει ότι την προωθείτε μόνο σε ιστότοπους. Μπορείτε επίσης να προωθήσετε την αναφορά σας στην καθημερινή σας ζωή. Υπάρχουν πολλοί τρόποι να την προωθήσετε στο σπίτι, στη δουλειά και σε πολλά άλλα μέρη όπου μπορείτε να συγκεντρώσετε περισσότερες υπογραφές. Για παράδειγμα, μέσω αφισών στις βιβλιοθήκες της πόλης σας, ή ακόμη και κάνοντας προώθηση στη δουλειά και ενημερώνοντας τους ανθρώπους ότι έχετε δημιουργήσει μια καμπάνια για ένα συγκεκριμένο θέμα.</p></li>
</ul>
HTML,
            ],
            'es' => [
                'title' => 'Cómo escribir una petición',
                'slug' => 'how-to-write-a-petition',
                'content' => <<<'HTML'
<h2>Introducción</h2>
<p>Cada uno de nosotros, aquellos que por una u otra razón se sienten obligados a protestar contra algo y quieren actuar. Algunos quieren que sus políticos renuncien a su cargo, mientras que otros quieren expresar su apoyo a un político, y algunos quieren que su programa de televisión favorito vuelva a emitirse, y otros quieren ver la música de su banda favorita en su ciudad natal.</p>
<p>Muchos de nosotros también creemos en algo y tenemos valores. <em>Online Petition</em> se encarga de garantizar que estos valores no se violen y que los derechos no se violen.<br>La naturaleza, la economía y la política y los derechos humanos están en el centro de nuestra atención.</p>
<p>Escribir una petición no es tan difícil como podrías pensar.</p>
<p>Muchos activistas han ganado su batalla con <em>Online Petition</em>, es decir, usando nuestra plataforma, que es simple y potente. Es difícil decir exactamente qué hace que una petición sea firmada por muchas personas y alcance el resultado. En algunos casos es solo suerte. En otros es cuestión de tiempo. Sin embargo, es posible extraer algunos principios generales y pautas para escribir una buena petición.</p>
<h2>Pautas para escribir una buena petición</h2>
<p>Aquí tienes algunos de los mejores consejos del equipo de <em>Online Petition</em>:</p>
<ul>
<li><p>Escribe tu petición de forma clara y concisa. Muchos de los posibles firmantes de tu petición a menudo no disponen de mucho tiempo. Es importante que en la parte introductoria de la petición se muestre de manera concisa el objetivo principal. En el primer párrafo debes especificar los objetivos que la petición pretende alcanzar.</p></li>
<li><p>Corrección ortográfica<br>En muchos sitios no te preocupas en absoluto de hacer una corrección ortográfica de la petición publicada en línea. Si quieres que la gente se tome en serio tu petición, debes dedicar un poco de tu tiempo a asegurarte de que el formulario esté correctamente editado y, sobre todo, presentado al público de forma seria y profesional.</p></li>
<li><p>Usa "Internet" para difundirla.<br>Anuncia y promociona tu petición usando el correo electrónico y las redes sociales como Facebook y Twitter. Te sorprenderá la capacidad de difusión y divulgación de las redes sociales. Pide también a todos tus contactos que hablen de tu petición a sus amigos. Esta forma de publicidad llamada "marketing viral" es la forma más eficaz de promocionar un tema en la web.</p></li>
<li><p>¡Intenta ser respetuoso con los demás!<br>Es importante que expreses claramente los objetivos en el texto de la petición. Intenta hacerlo de una manera que respete las ideas de los demás. Muchos posibles firmantes, aunque apoyen los objetivos, a menudo se molestan por peticiones que son ofensivas en su contenido.</p></li>
<li><p>Especifica tus objetivos<br>Indica con precisión cuáles son los objetivos de tu petición y con qué fin utilizarás la petición. Indica a quién va dirigida la petición.</p></li>
<li><p>Promoción fuera de línea de una petición<br>El hecho de que hayas creado una petición en línea no significa que solo la promociones en sitios web. También puedes promocionar tu petición en tu vida diaria. Hay muchas maneras de promocionarla en casa, en el trabajo y en muchos otros lugares donde puedes recoger más firmas. Por ejemplo, mediante carteles en las bibliotecas de tu ciudad, o incluso haciendo promoción en el trabajo y dando a conocer a la gente que has creado una campaña sobre un determinado tema.</p></li>
</ul>
HTML,
            ],
            'fr' => [
                'title' => 'Comment rédiger une pétition',
                'slug' => 'how-to-write-a-petition',
                'content' => <<<'HTML'
<h2>Introduction</h2>
<p>Chacun de nous, ceux qui pour une raison ou une autre se sentent obligés de protester contre quelque chose et veulent agir. Certains veulent que leurs politiciens démissionnent de leur fonction, tandis que d'autres veulent exprimer leur soutien à un politicien, et certains veulent que leur émission de télévision préférée soit à nouveau diffusée, et d'autres encore veulent voir la musique de leur groupe préféré dans leur ville natale.</p>
<p>Beaucoup d'entre nous croient aussi en quelque chose et ont des valeurs. <em>Online Petition</em> veille à ce que ces valeurs ne soient pas violées et que les droits ne soient pas violés.<br>La nature, l'économie et la politique et les droits de l'homme sont au centre de notre attention.</p>
<p>Rédiger une pétition n'est pas aussi difficile que vous pourriez le penser.</p>
<p>De nombreux militants ont gagné leur combat avec <em>Online Petition</em>, c'est-à-dire en utilisant notre plateforme, qui est simple et puissante. Il est difficile de dire exactement ce qui fait qu'une pétition est signée par de nombreuses personnes et atteint le résultat. Dans certains cas, c'est juste de la chance. Dans d'autres, c'est une question de moment. Cependant, il est possible de dégager quelques principes généraux et lignes directrices pour rédiger une bonne pétition.</p>
<h2>Lignes directrices pour rédiger une bonne pétition</h2>
<p>Voici quelques-uns des meilleurs conseils de l'équipe d'<em>Online Petition</em> :</p>
<ul>
<li><p>Rédigez votre pétition de manière claire et concise. Beaucoup des signataires potentiels de votre pétition ne disposent souvent pas de beaucoup de temps. Il est important que dans la partie introductive de la pétition soit présenté de manière concise l'objectif principal. Dans le premier paragraphe, vous devez préciser les objectifs que la pétition vise à atteindre.</p></li>
<li><p>Vérification orthographique<br>Sur de nombreux sites, vous ne vous souciez pas du tout de faire une vérification orthographique de la pétition publiée en ligne. Si vous voulez que les gens prennent votre pétition au sérieux, vous devez consacrer un peu de votre temps à vous assurer que le formulaire est correctement édité et surtout proposé au public de manière sérieuse et professionnelle.</p></li>
<li><p>Utilisez "Internet" pour la diffuser.<br>Faites de la publicité et promouvez votre pétition en utilisant le courrier électronique et les réseaux sociaux comme Facebook et Twitter. Vous serez surpris par la capacité de diffusion et de publicité des réseaux sociaux. Demandez également à tous vos contacts de parler de votre pétition à leurs amis. Cette forme de publicité appelée "marketing viral" est le moyen le plus efficace de faire la promotion d'un sujet sur le web.</p></li>
<li><p>Essayez d'être respectueux des autres !<br>Il est important que vous exprimiez clairement les objectifs dans le texte de la pétition. Essayez de le faire d'une manière qui respecte les idées des autres. De nombreux signataires potentiels, bien que soutenant les objectifs, sont souvent agacés par les pétitions dont le contenu est offensant.</p></li>
<li><p>Précisez vos objectifs<br>Indiquez précisément quels sont les objectifs de votre pétition et à quelle fin vous utiliserez la pétition. Indiquez à qui s'adresse la pétition.</p></li>
<li><p>Promotion hors ligne d'une pétition<br>Le fait que vous ayez créé une pétition en ligne ne signifie pas que vous ne la promouvez que sur des sites web. Vous pouvez également promouvoir votre pétition dans votre vie quotidienne. Il existe de nombreuses façons de la promouvoir à la maison, au travail et dans de nombreux autres endroits où vous pouvez recueillir plus de signatures. Par exemple, au moyen d'affiches dans les bibliothèques de votre ville, ou même en faisant la promotion au travail et en informant les gens que vous avez créé une campagne sur un certain sujet.</p></li>
</ul>
HTML,
            ],
            'it' => [
                'title' => 'Come scrivere una petizione',
                'slug' => 'how-to-write-a-petition',
                'content' => <<<'HTML'
<h2>Introduzione</h2>
<p>Ognuno di noi, coloro che per un motivo o per l'altro si sentono spinti a protestare contro qualcosa e vogliono agire. Alcuni vogliono che i loro politici si dimettano dal loro ruolo, mentre altri vogliono esprimere il loro sostegno a un politico, e alcuni vogliono che il loro programma televisivo preferito torni in onda e altri ancora vogliono vedere la musica della loro band preferita nella loro città natale.</p>
<p>Molti di noi credono anche in qualcosa e hanno dei valori. <em>Online Petition</em> si prende cura di garantire che questi valori non vengano violati e che i diritti non vengano violati.<br>La natura, l'economia e la politica e i diritti umani sono al centro della nostra attenzione.</p>
<p>Scrivere una petizione non è così difficile come si potrebbe pensare.</p>
<p>Molti attivisti hanno vinto la loro battaglia con <em>Online Petition</em>, cioè utilizzando la nostra piattaforma, che è semplice e potente. È difficile dire esattamente cosa fa sì che una petizione venga firmata da molte persone e raggiunga il risultato. In alcuni casi è solo fortuna. In altri è il momento giusto. Tuttavia, è possibile trarre alcuni principi generali e linee guida per scrivere una buona petizione.</p>
<h2>Linee guida per scrivere una buona petizione</h2>
<p>Ecco alcuni dei migliori consigli del team di <em>Online Petition</em>:</p>
<ul>
<li><p>Scrivi la tua petizione in modo chiaro e conciso. Molti dei potenziali firmatari della tua petizione spesso non hanno molto tempo a disposizione. È importante che nella parte introduttiva della petizione venga mostrato in modo conciso l'obiettivo principale. Nel primo paragrafo devi specificare gli obiettivi che la petizione mira a raggiungere.</p></li>
<li><p>Controllo ortografico<br>Su molti siti non ti preoccupi affatto di fare il controllo ortografico della petizione pubblicata online. Se vuoi che le persone prendano sul serio la tua petizione, devi dedicare un po' del tuo tempo per assicurarti che il modulo sia correttamente redatto e soprattutto proposto al pubblico in modo serio e professionale.</p></li>
<li><p>Usa "Internet" per diffonderla.<br>Pubblicizza e promuovi la tua petizione usando l'email e i social network come Facebook e Twitter. Rimarrai sorpreso dalla capacità di diffondere e pubblicizzare dei social network. Chiedi anche a tutti i tuoi contatti di parlare della tua petizione ai loro amici. Questa forma di pubblicità chiamata "marketing virale" è il modo più efficace per pubblicizzare un argomento sul web.</p></li>
<li><p>Cerca di essere rispettoso degli altri!<br>È importante che tu esprima chiaramente gli obiettivi nel testo della petizione. Cerca di farlo in un modo che rispetti le idee degli altri. Molti potenziali firmatari, pur sostenendo gli obiettivi, sono spesso infastiditi da petizioni che hanno un contenuto offensivo.</p></li>
<li><p>Specifica i tuoi obiettivi<br>Indica con precisione quali sono gli obiettivi della tua petizione e per quale scopo utilizzerai la petizione. Indica a chi è rivolta la petizione.</p></li>
<li><p>Promozione offline di una petizione<br>Il fatto che tu abbia creato una petizione online non significa che tu la promuova solo sui siti web. Puoi anche promuovere la tua petizione nella tua vita quotidiana. Ci sono molti modi per promuoverla a casa, al lavoro e in molti altri luoghi dove puoi raccogliere più firme. Ad esempio, attraverso manifesti nelle biblioteche della tua città, o anche facendo promozione al lavoro e facendo sapere alle persone che hai creato una campagna su un determinato argomento.</p></li>
</ul>
HTML,
            ],
            'nl' => [
                'title' => 'Hoe schrijf je een petitie',
                'slug' => 'how-to-write-a-petition',
                'content' => <<<'HTML'
<h2>Inleiding</h2>
<p>Ieder van ons, degenen die zich om de een of andere reden gedwongen voelen om tegen iets te protesteren en willen handelen. Sommigen willen dat hun politici aftreden uit hun functie, terwijl anderen hun steun aan een politicus willen uitspreken, en sommigen willen dat hun favoriete tv-programma weer wordt uitgezonden, en weer anderen willen de muziek van hun favoriete band in hun geboortestad zien.</p>
<p>Velen van ons geloven ook in iets en hebben waarden. <em>Online Petition</em> zorgt ervoor dat deze waarden niet worden geschonden en dat de rechten niet worden geschonden.<br>De natuur, de economie en de politiek en de mensenrechten staan centraal in onze aandacht.</p>
<p>Een petitie schrijven is niet zo moeilijk als je zou denken.</p>
<p>Veel activisten hebben hun strijd gewonnen met <em>Online Petition</em>, dat wil zeggen door gebruik te maken van ons platform, dat eenvoudig en krachtig is. Het is moeilijk om precies te zeggen wat ervoor zorgt dat een petitie door veel mensen wordt ondertekend en het resultaat bereikt. In sommige gevallen is het gewoon geluk. In andere is het timing. Toch is het mogelijk om enkele algemene principes en richtlijnen op te stellen voor het schrijven van een goede petitie.</p>
<h2>Richtlijnen voor het schrijven van een goede petitie</h2>
<p>Hier zijn enkele van de beste tips van het team van <em>Online Petition</em>:</p>
<ul>
<li><p>Schrijf je petitie duidelijk en beknopt. Veel van de potentiële ondertekenaars van je petitie hebben vaak niet veel tijd tot hun beschikking. Het is belangrijk dat in het inleidende deel van de petitie het hoofddoel op beknopte wijze wordt weergegeven. In de eerste alinea moet je de doelstellingen specificeren die de petitie beoogt te bereiken.</p></li>
<li><p>Spellingcontrole<br>Op veel sites maak je je helemaal geen zorgen om een spellingcontrole uit te voeren van de online geplaatste petitie. Als je wilt dat mensen je petitie serieus nemen, moet je een beetje van je tijd besteden om ervoor te zorgen dat het formulier correct is bewerkt en vooral op een serieuze en professionele manier aan het publiek wordt gepresenteerd.</p></li>
<li><p>Gebruik "internet" om haar te verspreiden.<br>Adverteer en promoot je petitie met behulp van e-mail en sociale netwerksites zoals Facebook en Twitter. Je zult verrast zijn over het vermogen van sociale netwerken om te verspreiden en bekend te maken. Vraag ook al je contacten om hun vrienden over je petitie te vertellen. Deze vorm van reclame die "virale marketing" wordt genoemd, is de meest effectieve manier om een onderwerp op het web te promoten.</p></li>
<li><p>Probeer respectvol te zijn tegenover anderen!<br>Het is belangrijk dat je de doelstellingen duidelijk uitdrukt in de tekst van de petitie. Probeer het te doen op een manier die de ideeën van anderen respecteert. Veel potentiële ondertekenaars, hoewel ze de doelstellingen steunen, ergeren zich vaak aan petities die beledigend zijn in hun inhoud.</p></li>
<li><p>Specificeer je doelstellingen<br>Geef nauwkeurig aan wat de doelstellingen van je petitie zijn en voor welk doel je de petitie zult gebruiken. Geef aan tot wie de petitie is gericht.</p></li>
<li><p>Offline promotie van een petitie<br>Het feit dat je een online petitie hebt gemaakt, betekent niet dat je deze alleen op websites promoot. Je kunt je petitie ook in je dagelijks leven promoten. Er zijn veel manieren om deze te promoten thuis, op het werk en op veel andere plaatsen waar je meer handtekeningen kunt verzamelen. Bijvoorbeeld via posters in de bibliotheken van je stad, of zelfs door promotie te maken op het werk en mensen bewust te maken dat je een campagne over een bepaald onderwerp hebt gemaakt.</p></li>
</ul>
HTML,
            ],
            'pl' => [
                'title' => 'Jak napisać petycję',
                'slug' => 'how-to-write-a-petition',
                'content' => <<<'HTML'
<h2>Wprowadzenie</h2>
<p>Każdy z nas, ci, którzy z tego czy innego powodu czują się zmuszeni protestować przeciwko czemuś i chcą działać. Niektórzy chcą, aby ich politycy ustąpili ze swojej funkcji, podczas gdy inni chcą wyrazić swoje poparcie dla polityka, a niektórzy chcą, aby ich ulubiony program telewizyjny ponownie pojawił się na antenie, a jeszcze inni chcą zobaczyć muzykę swojego ulubionego zespołu w swoim rodzinnym mieście.</p>
<p>Wielu z nas również w coś wierzy i wyznaje wartości. <em>Online Petition</em> dba o to, aby te wartości nie były naruszane i aby prawa nie były naruszane.<br>Przyroda, gospodarka i polityka oraz prawa człowieka znajdują się w centrum naszej uwagi.</p>
<p>Napisanie petycji nie jest tak trudne, jak mogłoby się wydawać.</p>
<p>Wielu aktywistów wygrało swoją walkę z <em>Online Petition</em>, to znaczy korzystając z naszej platformy, która jest prosta i potężna. Trudno powiedzieć dokładnie, co sprawia, że petycja jest podpisywana przez wiele osób i osiąga rezultat. W niektórych przypadkach to po prostu szczęście. W innych to kwestia czasu. Można jednak wyciągnąć pewne ogólne zasady i wskazówki dotyczące pisania dobrej petycji.</p>
<h2>Wskazówki dotyczące pisania dobrej petycji</h2>
<p>Oto kilka najlepszych wskazówek od zespołu <em>Online Petition</em>:</p>
<ul>
<li><p>Pisz swoją petycję jasno i zwięźle. Wielu potencjalnych sygnatariuszy twojej petycji często nie dysponuje dużą ilością czasu. Ważne jest, aby we wstępnej części petycji w zwięzły sposób przedstawić główny cel. W pierwszym akapicie musisz określić cele, które petycja ma osiągnąć.</p></li>
<li><p>Sprawdzanie pisowni<br>Na wielu stronach wcale nie martwisz się o sprawdzenie pisowni petycji opublikowanej online. Jeśli chcesz, aby ludzie poważnie potraktowali twoją petycję, musisz poświęcić trochę swojego czasu, aby upewnić się, że formularz jest poprawnie zredagowany, a przede wszystkim przedstawiony publiczności w poważny i profesjonalny sposób.</p></li>
<li><p>Użyj "internetu", aby ją rozpowszechnić.<br>Reklamuj i promuj swoją petycję, korzystając z poczty e-mail oraz serwisów społecznościowych, takich jak Facebook i Twitter. Będziesz zaskoczony zdolnością sieci społecznościowych do rozpowszechniania i nagłaśniania. Poproś również wszystkie swoje kontakty, aby opowiedziały o twojej petycji swoim znajomym. Ta forma reklamy zwana "marketingiem wirusowym" jest najskuteczniejszym sposobem reklamowania tematu w sieci.</p></li>
<li><p>Staraj się szanować innych!<br>Ważne jest, abyś jasno wyraził cele w tekście petycji. Staraj się to robić w sposób, który szanuje poglądy innych. Wielu potencjalnych sygnatariuszy, choć popierają cele, często jest zirytowanych petycjami, które są obraźliwe w swojej treści.</p></li>
<li><p>Określ swoje cele<br>Wskaż precyzyjnie, jakie są cele twojej petycji i do jakiego celu wykorzystasz petycję. Wskaż, do kogo jest skierowana petycja.</p></li>
<li><p>Promocja petycji offline<br>Fakt, że utworzyłeś petycję online, nie oznacza, że promujesz ją tylko na stronach internetowych. Możesz również promować swoją petycję w codziennym życiu. Istnieje wiele sposobów na promowanie jej w domu, w pracy i w wielu innych miejscach, gdzie możesz zebrać więcej podpisów. Na przykład poprzez plakaty w bibliotekach twojego miasta, a nawet prowadząc promocję w pracy i informując ludzi, że utworzyłeś kampanię na określony temat.</p></li>
</ul>
HTML,
            ],
            'pt' => [
                'title' => 'Como escrever uma petição',
                'slug' => 'how-to-write-a-petition',
                'content' => <<<'HTML'
<h2>Introdução</h2>
<p>Cada um de nós, aqueles que por uma razão ou outra se sentem compelidos a protestar contra algo e querem agir. Alguns querem que os seus políticos renunciem ao seu cargo, enquanto outros querem expressar o seu apoio a um político, e alguns querem que o seu programa de televisão favorito volte a ser transmitido e outros ainda querem ver a música da sua banda favorita na sua cidade natal.</p>
<p>Muitos de nós também acreditam em algo e têm valores. O <em>Online Petition</em> preocupa-se em garantir que esses valores não sejam violados e que os direitos não sejam violados.<br>A natureza, a economia e a política e os direitos humanos estão no centro da nossa atenção.</p>
<p>Escrever uma petição não é tão difícil como se possa pensar.</p>
<p>Muitos ativistas ganharam a sua batalha com o <em>Online Petition</em>, ou seja, usando a nossa plataforma, que é simples e poderosa. É difícil dizer exatamente o que faz com que uma petição seja assinada por muitas pessoas e alcance o resultado. Em alguns casos é apenas sorte. Noutros é uma questão de tempo. No entanto, é possível extrair alguns princípios gerais e diretrizes para escrever uma boa petição.</p>
<h2>Diretrizes para escrever uma boa petição</h2>
<p>Aqui estão algumas das melhores dicas da equipa do <em>Online Petition</em>:</p>
<ul>
<li><p>Escreva a sua petição de forma clara e concisa. Muitos dos potenciais signatários da sua petição muitas vezes não têm muito tempo à sua disposição. É importante que na parte introdutória da petição seja apresentado de forma concisa o objetivo principal. No primeiro parágrafo deve especificar os objetivos que a petição pretende alcançar.</p></li>
<li><p>Verificação ortográfica<br>Em muitos sites não se preocupa de todo em fazer a verificação ortográfica da petição publicada online. Se quiser que as pessoas levem a sério a sua petição, tem de dedicar um pouco do seu tempo para garantir que o formulário está corretamente editado e, sobretudo, apresentado ao público de forma séria e profissional.</p></li>
<li><p>Use a "Internet" para a divulgar.<br>Anuncie e promova a sua petição usando o e-mail e redes sociais como o Facebook e o Twitter. Ficará surpreendido com a capacidade de divulgação e publicitação das redes sociais. Peça também a todos os seus contactos para falarem da sua petição aos seus amigos. Esta forma de publicidade chamada "marketing viral" é a forma mais eficaz de publicitar um tema na web.</p></li>
<li><p>Tente ser respeitoso com os outros!<br>É importante que exprima claramente os objetivos no texto da petição. Tente fazê-lo mas de uma forma que respeite as ideias dos outros. Muitos potenciais signatários, embora apoiando os objetivos, ficam muitas vezes incomodados com petições que são ofensivas no seu conteúdo.</p></li>
<li><p>Especifique os seus objetivos<br>Indique com precisão quais são os objetivos da sua petição e para que fim utilizará a petição. Indique a quem se dirige a petição.</p></li>
<li><p>Promoção offline de uma petição<br>O facto de ter criado uma petição online não significa que a promova apenas em sites. Também pode promover a sua petição na sua vida quotidiana. Há muitas formas de a promover em casa, no trabalho e em muitos outros lugares onde pode recolher mais assinaturas. Por exemplo, através de cartazes nas bibliotecas da sua cidade, ou mesmo fazendo promoção no trabalho e dando a conhecer às pessoas que criou uma campanha sobre um determinado tema.</p></li>
</ul>
HTML,
            ],
            'ro' => [
                'title' => 'Cum să scrii o petiție',
                'slug' => 'how-to-write-a-petition',
                'content' => <<<'HTML'
<h2>Introducere</h2>
<p>Fiecare dintre noi, cei care dintr-un motiv sau altul se simt îndemnați să protesteze împotriva a ceva și vor să acționeze. Unii vor ca politicienii lor să demisioneze din funcția lor, în timp ce alții vor să își exprime sprijinul pentru un politician, iar unii vor ca programul lor TV preferat să fie din nou difuzat și alții vor să vadă muzica trupei lor preferate în orașul lor natal.</p>
<p>Mulți dintre noi credem de asemenea în ceva și avem valori. <em>Online Petition</em> are grijă să se asigure că aceste valori nu sunt încălcate și că drepturile nu sunt încălcate.<br>Natura, economia și politica și drepturile omului se află în centrul atenției noastre.</p>
<p>A scrie o petiție nu este atât de dificil pe cât ai putea crede.</p>
<p>Mulți activiști și-au câștigat bătălia cu <em>Online Petition</em>, adică folosind platforma noastră, care este simplă și puternică. Este greu de spus exact ce face ca o petiție să fie semnată de mulți oameni și să atingă rezultatul. În unele cazuri este doar noroc. În altele este momentul potrivit. Cu toate acestea, este posibil să se extragă câteva principii generale și linii directoare pentru a scrie o petiție bună.</p>
<h2>Linii directoare pentru a scrie o petiție bună</h2>
<p>Iată câteva dintre cele mai bune sfaturi de la echipa <em>Online Petition</em>:</p>
<ul>
<li><p>Scrie-ți petiția clar și concis. Mulți dintre potențialii semnatari ai petiției tale adesea nu au mult timp la dispoziție. Este important ca în partea introductivă a petiției să fie prezentat în mod concis obiectivul principal. În primul paragraf trebuie să specifici obiectivele pe care petiția urmărește să le atingă.</p></li>
<li><p>Verificarea ortografică<br>Pe multe site-uri nu te preocupi deloc să faci verificarea ortografică a petiției publicate online. Dacă vrei ca oamenii să ia în serios petiția ta, trebuie să dedici puțin din timpul tău pentru a te asigura că formularul este corect redactat și mai ales prezentat publicului într-un mod serios și profesional.</p></li>
<li><p>Folosește "internetul" pentru a o difuza.<br>Fă publicitate și promovează-ți petiția folosind e-mailul și rețelele de socializare precum Facebook și Twitter. Vei fi surprins de capacitatea de difuzare și de promovare a rețelelor de socializare. Cere, de asemenea, tuturor contactelor tale să le spună prietenilor lor despre petiția ta. Această formă de publicitate numită "marketing viral" este cel mai eficient mod de a face publicitate unui subiect pe web.</p></li>
<li><p>Încearcă să îi respecți pe ceilalți!<br>Este important să exprimi clar obiectivele în textul petiției. Încearcă să o faci însă într-un mod care respectă ideile celorlalți. Mulți potențiali semnatari, deși susțin obiectivele, sunt adesea deranjați de petiții care au un conținut ofensator.</p></li>
<li><p>Specifică-ți obiectivele<br>Indică precis care sunt obiectivele petiției tale și în ce scop vei folosi petiția. Indică cui îi este adresată petiția.</p></li>
<li><p>Promovarea offline a unei petiții<br>Faptul că ai creat o petiție online nu înseamnă că o promovezi doar pe site-uri web. Îți poți promova petiția și în viața ta de zi cu zi. Există multe modalități de a o promova acasă, la serviciu și în multe alte locuri unde poți strânge mai multe semnături. De exemplu, prin afișe în bibliotecile orașului tău, sau chiar făcând promovare la serviciu și informând oamenii că ai creat o campanie pe un anumit subiect.</p></li>
</ul>
HTML,
            ],
            'ru' => [
                'title' => 'Как написать петицию',
                'slug' => 'how-to-write-a-petition',
                'content' => <<<'HTML'
<h2>Введение</h2>
<p>Каждый из нас, те, кто по той или иной причине чувствует необходимость протестовать против чего-либо и хочет действовать. Одни хотят, чтобы их политики ушли со своих постов, другие хотят выразить поддержку политику, кто-то хочет, чтобы их любимая телепередача снова вышла в эфир, а кто-то хочет услышать музыку своей любимой группы в родном городе.</p>
<p>Многие из нас также во что-то верят и имеют ценности. <em>Online Petition</em> заботится о том, чтобы эти ценности не нарушались и чтобы права не нарушались.<br>Природа, экономика и политика и права человека находятся в центре нашего внимания.</p>
<p>Написать петицию не так сложно, как может показаться.</p>
<p>Многие активисты выиграли свою битву с <em>Online Petition</em>, то есть используя нашу платформу, которая проста и мощна. Трудно сказать точно, что делает так, что петицию подписывают многие люди и она достигает результата. В некоторых случаях это просто удача. В других — время. Тем не менее можно вывести некоторые общие принципы и рекомендации для написания хорошей петиции.</p>
<h2>Рекомендации по написанию хорошей петиции</h2>
<p>Вот некоторые из лучших советов от команды <em>Online Petition</em>:</p>
<ul>
<li><p>Пишите свою петицию ясно и кратко. У многих потенциальных подписантов вашей петиции часто не так много свободного времени. Важно, чтобы во вступительной части петиции в сжатой форме была изложена главная цель. В первом абзаце вы должны указать цели, которых петиция стремится достичь.</p></li>
<li><p>Проверка орфографии<br>На многих сайтах вы совсем не беспокоитесь о проверке орфографии размещённой онлайн петиции. Если вы хотите, чтобы люди серьёзно отнеслись к вашей петиции, вам нужно потратить немного своего времени, чтобы убедиться, что форма правильно отредактирована и, что особенно важно, представлена публике серьёзным и профессиональным образом.</p></li>
<li><p>Используйте "интернет", чтобы распространить её.<br>Рекламируйте и продвигайте свою петицию с помощью электронной почты и социальных сетей, таких как Facebook и Twitter. Вы будете удивлены способностью социальных сетей распространять и предавать огласке. Также попросите всех своих знакомых рассказать о вашей петиции своим друзьям. Эта форма рекламы, называемая "вирусным маркетингом", является самым эффективным способом рекламы темы в сети.</p></li>
<li><p>Старайтесь уважать других!<br>Важно, чтобы вы чётко выразили цели в тексте петиции. Старайтесь делать это так, чтобы уважать идеи других. Многие потенциальные подписанты, хотя и поддерживают цели, часто раздражаются из-за петиций, оскорбительных по своему содержанию.</p></li>
<li><p>Укажите свои цели<br>Точно укажите, каковы цели вашей петиции и для какой цели вы будете использовать петицию. Укажите, кому адресована петиция.</p></li>
<li><p>Офлайн-продвижение петиции<br>Тот факт, что вы создали онлайн-петицию, не означает, что вы продвигаете её только на веб-сайтах. Вы также можете продвигать свою петицию в повседневной жизни. Есть много способов продвигать её дома, на работе и во многих других местах, где вы можете собрать больше подписей. Например, с помощью плакатов в библиотеках вашего города или даже проводя продвижение на работе и информируя людей о том, что вы создали кампанию по определённой теме.</p></li>
</ul>
HTML,
            ],
            'sv' => [
                'title' => 'Hur man skriver en namninsamling',
                'slug' => 'how-to-write-a-petition',
                'content' => <<<'HTML'
<h2>Introduktion</h2>
<p>Var och en av oss, de som av en eller annan anledning känner sig tvungna att protestera mot något och vill agera. Vissa vill att deras politiker ska avgå från sin roll, medan andra vill uttrycka sitt stöd för en politiker, och vissa vill att deras favoritprogram på tv ska sändas igen och ytterligare andra vill se sitt favoritbands musik i sin hemstad.</p>
<p>Många av oss tror också på något och har värderingar. <em>Online Petition</em> ser till att dessa värderingar inte kränks och att rättigheterna inte kränks.<br>Naturen, ekonomin och politiken och de mänskliga rättigheterna står i centrum för vår uppmärksamhet.</p>
<p>Att skriva en namninsamling är inte så svårt som man kan tro.</p>
<p>Många aktivister har vunnit sin kamp med <em>Online Petition</em>, det vill säga genom att använda vår plattform, som är enkel och kraftfull. Det är svårt att säga exakt vad som gör att en namninsamling undertecknas av många människor och når resultatet. I vissa fall är det bara tur. I andra är det rätt tidpunkt. Det är dock möjligt att dra några allmänna principer och riktlinjer för att skriva en bra namninsamling.</p>
<h2>Riktlinjer för att skriva en bra namninsamling</h2>
<p>Här är några av de bästa tipsen från teamet på <em>Online Petition</em>:</p>
<ul>
<li><p>Skriv din namninsamling tydligt och koncist. Många av de potentiella undertecknarna av din namninsamling har ofta inte mycket tid till sitt förfogande. Det är viktigt att huvudmålet presenteras på ett koncist sätt i den inledande delen av namninsamlingen. I det första stycket måste du ange de mål som namninsamlingen syftar till att uppnå.</p></li>
<li><p>Stavningskontroll<br>På många sidor bryr du dig inte alls om att göra en stavningskontroll av den namninsamling som lades ut online. Om du vill att folk ska ta din namninsamling på allvar måste du lägga lite av din tid på att se till att formuläret är korrekt redigerat och framför allt presenterat för allmänheten på ett seriöst och professionellt sätt.</p></li>
<li><p>Använd "internet" för att sprida den.<br>Annonsera och marknadsför din namninsamling med hjälp av e-post och sociala nätverkssidor som Facebook och Twitter. Du kommer att bli förvånad över de sociala nätverkens förmåga att sprida och offentliggöra. Be också alla dina kontakter att berätta för sina vänner om din namninsamling. Denna form av reklam som kallas "viral marknadsföring" är det mest effektiva sättet att göra reklam för ett ämne på webben.</p></li>
<li><p>Försök att vara respektfull mot andra!<br>Det är viktigt att du tydligt uttrycker målen i texten till namninsamlingen. Försök att göra det på ett sätt som respekterar andras idéer. Många potentiella undertecknare, även om de stöder målen, blir ofta irriterade över namninsamlingar som är stötande i sitt innehåll.</p></li>
<li><p>Ange dina mål<br>Ange exakt vilka målen för din namninsamling är och i vilket syfte du kommer att använda namninsamlingen. Ange till vem namninsamlingen är riktad.</p></li>
<li><p>Offline-marknadsföring av en namninsamling<br>Det faktum att du har skapat en namninsamling online betyder inte att du bara marknadsför den på webbplatser. Du kan också marknadsföra din namninsamling i ditt dagliga liv. Det finns många sätt att marknadsföra den hemma, på jobbet och på många andra platser där du kan samla in fler underskrifter. Till exempel genom affischer i bibliotek i din stad, eller till och med genom att göra marknadsföring på jobbet och göra folk medvetna om att du har skapat en kampanj om ett visst ämne.</p></li>
</ul>
HTML,
            ],
            'tr' => [
                'title' => 'Bir dilekçe nasıl yazılır',
                'slug' => 'how-to-write-a-petition',
                'content' => <<<'HTML'
<h2>Giriş</h2>
<p>Her birimiz, şu ya da bu nedenle bir şeye karşı protesto etme ihtiyacı hisseden ve harekete geçmek isteyen kişiler. Bazıları politikacılarının görevlerinden istifa etmesini isterken, diğerleri bir politikacıya desteklerini ifade etmek ister, bazıları en sevdikleri televizyon programının yeniden yayınlanmasını ister ve kimileri de en sevdikleri grubun müziğini memleketlerinde görmek ister.</p>
<p>Birçoğumuz ayrıca bir şeye inanırız ve değerlerimiz vardır. <em>Online Petition</em>, bu değerlerin ihlal edilmemesini ve hakların ihlal edilmemesini sağlamaya özen gösterir.<br>Doğa, ekonomi ve siyaset ile insan hakları ilgimizin merkezindedir.</p>
<p>Bir dilekçe yazmak düşündüğünüz kadar zor değildir.</p>
<p>Birçok aktivist, basit ve güçlü olan platformumuzu kullanarak, yani <em>Online Petition</em> ile mücadelesini kazandı. Bir dilekçenin birçok kişi tarafından imzalanmasını ve sonuca ulaşmasını neyin sağladığını tam olarak söylemek zordur. Bazı durumlarda bu sadece şanstır. Bazılarında ise zamanlamadır. Yine de, iyi bir dilekçe yazmak için bazı genel ilkeler ve yönergeler çıkarmak mümkündür.</p>
<h2>İyi bir dilekçe yazmak için yönergeler</h2>
<p>İşte <em>Online Petition</em> ekibinden en iyi ipuçlarından bazıları:</p>
<ul>
<li><p>Dilekçenizi açık ve öz bir şekilde yazın. Dilekçenizin olası imzacılarının çoğu genellikle fazla zamana sahip değildir. Dilekçenin giriş bölümünde ana hedefin öz bir şekilde gösterilmesi önemlidir. İlk paragrafta dilekçenin ulaşmayı amaçladığı hedefleri belirtmelisiniz.</p></li>
<li><p>Yazım denetimi<br>Birçok sitede çevrimiçi yayınlanan dilekçenin yazım denetimini yapma konusunda hiç endişelenmezsiniz. İnsanların dilekçenizi ciddiye almasını istiyorsanız, formun doğru şekilde düzenlendiğinden ve özellikle de kamuya ciddi ve profesyonel bir şekilde sunulduğundan emin olmak için zamanınızın bir kısmını ayırmalısınız.</p></li>
<li><p>Yaymak için "interneti" kullanın.<br>Dilekçenizi e-posta ve Facebook ile Twitter gibi sosyal ağ siteleri kullanarak reklamını yapın ve tanıtın. Sosyal ağların yayma ve duyurma yeteneğine şaşıracaksınız. Ayrıca tüm bağlantılarınızdan dilekçeniz hakkında arkadaşlarına anlatmalarını isteyin. "Viral pazarlama" olarak adlandırılan bu reklam biçimi, web'de bir konunun reklamını yapmanın en etkili yoludur.</p></li>
<li><p>Başkalarına saygılı olmaya çalışın!<br>Dilekçenin metninde hedefleri açıkça ifade etmeniz önemlidir. Ancak bunu başkalarının fikirlerine saygı gösteren bir şekilde yapmaya çalışın. Birçok olası imzacı, hedefleri desteklese de, içerikleri rahatsız edici olan dilekçelerden sıklıkla rahatsız olur.</p></li>
<li><p>Hedeflerinizi belirtin<br>Dilekçenizin hedeflerinin tam olarak ne olduğunu ve dilekçeyi hangi amaçla kullanacağınızı belirtin. Dilekçenin kime yönelik olduğunu belirtin.</p></li>
<li><p>Bir dilekçenin çevrimdışı tanıtımı<br>Çevrimiçi bir dilekçe oluşturmuş olmanız, onu yalnızca web sitelerinde tanıttığınız anlamına gelmez. Dilekçenizi günlük hayatınızda da tanıtabilirsiniz. Daha fazla imza toplayabileceğiniz evde, işte ve birçok başka yerde onu tanıtmanın birçok yolu vardır. Örneğin, şehrinizin kütüphanelerindeki afişler aracılığıyla, hatta işte tanıtım yaparak ve insanlara belirli bir konuda bir kampanya oluşturduğunuzu bildirerek.</p></li>
</ul>
HTML,
            ],
        ];
    }
};
