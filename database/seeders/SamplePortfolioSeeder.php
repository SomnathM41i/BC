<?php

namespace Database\Seeders;

use App\Models\PortfolioProject;
use Illuminate\Database\Seeder;

/**
 * Illustrative portfolio entries used to lay out the public site.
 *
 * Every entry is flagged as a sample (is_sample = true) and is labelled as
 * such on the website. Replace these with real project records once the
 * details and photographs are confirmed; do not present them as completed work.
 *
 * Run with:  php artisan db:seed --class=SamplePortfolioSeeder
 */
class SamplePortfolioSeeder extends Seeder
{
    public function run(): void
    {
        $projects = [
            [
                'slug' => 'courtyard-family-home',
                'title' => 'Courtyard Family Home',
                'title_mr' => 'अंगण असलेले कौटुंबिक घर',
                'category' => 'residential',
                'location' => 'Concept study · Sangli district',
                'location_mr' => 'संकल्पना अभ्यास · सांगली जिल्हा',
                'summary' => 'A single-storey family home arranged around a shaded central courtyard, keeping living spaces cool and connected.',
                'summary_mr' => 'सावली देणाऱ्या मध्यवर्ती अंगणाभोवती रचलेले एकमजली कौटुंबिक घर, ज्यामुळे राहण्याच्या जागा थंड आणि जोडलेल्या राहतात.',
                'description' => "Planning started with the family's daily routine: a shared living and dining space, two bedrooms, a kitchen that opens to a utility yard, and a verandah for visitors. The rooms sit around a central courtyard, which brings daylight into the middle of the plan and lets hot air escape through the roof.\n\nThe structure is kept simple — load-bearing walls with a lightweight roof — so the design can be built with local labour and materials. Deep verandahs and small openings on the west side reduce afternoon heat.",
                'description_mr' => "नियोजन कुटुंबाच्या रोजच्या दिनक्रमापासून सुरू झाले: एकत्रित बैठक व जेवणाची जागा, दोन शयनकक्ष, उपयोगाच्या अंगणात उघडणारे स्वयंपाकघर आणि पाहुण्यांसाठी ओसरी. खोल्या मध्यवर्ती अंगणाभोवती आहेत, त्यामुळे आराखड्याच्या मध्यभागी नैसर्गिक प्रकाश येतो आणि गरम हवा छतावरून बाहेर जाते.\n\nसंरचना साधी ठेवली आहे — भारवाहक भिंती आणि हलके छत — जेणेकरून स्थानिक कामगार व साहित्याने बांधकाम करता येईल. पश्चिमेकडील खोल ओसऱ्या व लहान खिडक्या दुपारची उष्णता कमी करतात.",
                'cover_image' => 'images/site/portfolio/takoradi.jpg',
                'sort_order' => 1,
            ],
            [
                'slug' => 'modern-gable-residence',
                'title' => 'Modern Gable Residence',
                'title_mr' => 'आधुनिक गॅबल निवास',
                'category' => 'modern-homes',
                'location' => 'Concept study · Atpadi',
                'location_mr' => 'संकल्पना अभ्यास · आटपाडी',
                'summary' => 'A two-storey home with a steep gable roof, deep eaves and a simple palette of plaster, stone and timber.',
                'summary_mr' => 'तीव्र उताराचे छत, खोल वत्सरे आणि प्लास्टर, दगड व लाकूड अशा साध्या साहित्य संचासह दुमजली घर.',
                'description' => "The gable form responds to heavy monsoon rain, throwing water clear of the walls while giving the upper floor a generous ceiling height. Living, dining and kitchen occupy the ground floor; bedrooms and a study sit above.\n\nThe front elevation keeps ornament to a minimum. A stone base, a smooth plaster body and timber screens over the staircase window do most of the work.",
                'description_mr' => "गॅबल स्वरूप मुसळधार पावसाला तोंड देते — पाणी भिंतींपासून दूर टाकते आणि वरच्या मजल्याला उंच छत देते. तळमजल्यावर बैठक, जेवण व स्वयंपाकघर; वर शयनकक्ष व अभ्यासिका.\n\nपुढील दर्शनी भागात अलंकार कमी ठेवले आहेत. दगडी पाया, गुळगुळीत प्लास्टर आणि जिन्याच्या खिडकीवरील लाकडी जाळी एवढेच बोलके.",
                'cover_image' => 'images/site/portfolio/villa-render.jpg',
                'sort_order' => 2,
            ],
            [
                'slug' => 'terrace-house',
                'title' => 'Terrace House',
                'title_mr' => 'गच्ची असलेले घर',
                'category' => 'residential',
                'location' => 'Concept study · Sangli',
                'location_mr' => 'संकल्पना अभ्यास · सांगली',
                'summary' => 'A compact urban house that uses stepped terraces to bring light and air into a narrow plot.',
                'summary_mr' => 'अरुंद भूखंडात प्रकाश व हवा आणण्यासाठी टप्प्याटप्प्याने गच्च्या वापरणारे सुसंघटित शहरी घर.',
                'description' => "On a narrow plot the plan steps back at each floor, creating terraces for the bedrooms and a roof garden above. The staircase sits against the shared wall, freeing the other side for windows.\n\nRainwater from the terraces is collected and reused for the garden. The facade uses simple bands of plaster and railing to keep the composition calm.",
                'description_mr' => "अरुंद भूखंडावर प्रत्येक मजला मागे सरकतो, त्यामुळे शयनकक्षांसाठी गच्च्या आणि वर छतावरील बाग तयार होते. जिना सामायिक भिंतीलगत आहे, त्यामुळे दुसऱ्या बाजूला खिडक्यांना जागा मिळते.\n\nगच्च्यांवरील पावसाचे पाणी साठवून बागेसाठी वापरले जाते. दर्शनी भागात प्लास्टर व कठड्यांच्या साध्या पट्ट्यांमुळे रचना शांत राहते.",
                'cover_image' => 'images/site/portfolio/tadepalli.jpg',
                'sort_order' => 3,
            ],
            [
                'slug' => 'hillside-villa',
                'title' => 'Hillside Villa',
                'title_mr' => 'टेकडीवरील व्हिला',
                'category' => 'modern-homes',
                'location' => 'Concept study · Western Maharashtra',
                'location_mr' => 'संकल्पना अभ्यास · पश्चिम महाराष्ट्र',
                'summary' => 'A split-level villa that follows the slope of the site instead of flattening it.',
                'summary_mr' => 'भूखंडाचा उतार सपाट न करता त्याला अनुसरून उभारलेली विभाजित पातळ्यांची व्हिला.',
                'description' => "Rather than cut and fill the whole plot, the house steps down the slope in two levels. The lower level opens to a garden on the downhill side; the upper level holds the main living spaces and bedrooms.\n\nRetaining walls are kept short, and large openings face the view while a deep roof overhang shades them.",
                'description_mr' => "संपूर्ण भूखंड कापून सपाट करण्याऐवजी घर उतारावर दोन पातळ्यांत खाली उतरते. खालची पातळी उताराच्या बाजूच्या बागेत उघडते; वरच्या पातळीत मुख्य राहण्याच्या जागा व शयनकक्ष आहेत.\n\nआधारभिंती कमी ठेवल्या आहेत; मोठ्या खिडक्या दृश्याकडे आहेत आणि खोल छताचे वत्सरे त्यांना सावली देतात.",
                'cover_image' => 'images/site/portfolio/rainier.jpg',
                'sort_order' => 4,
            ],
            [
                'slug' => 'urban-row-house',
                'title' => 'Urban Row House',
                'title_mr' => 'शहरी रांगेचे घर',
                'category' => 'architectural-design',
                'location' => 'Concept study · Sangli city',
                'location_mr' => 'संकल्पना अभ्यास · सांगली शहर',
                'summary' => 'A narrow-frontage row house planned for cross-ventilation and a private internal court.',
                'summary_mr' => 'आडव्या हवेसाठी व खाजगी अंतर्गत अंगणासाठी नियोजित अरुंद दर्शनी रांगेचे घर.',
                'description' => "Row houses rarely get light from more than one side. This study places a small court in the middle of the plan, so every room borrows daylight and air from it.\n\nStorage is built into the walls between rooms, and the staircase acts as a buffer between the public front and the private rear.",
                'description_mr' => "रांगेच्या घरांना बहुतेक वेळा एकाच बाजूने प्रकाश मिळतो. या अभ्यासात आराखड्याच्या मध्यभागी लहान अंगण ठेवले आहे, त्यामुळे प्रत्येक खोलीला अंगणाकडून प्रकाश व हवा मिळते.\n\nखोल्यांमधील भिंतींमध्ये साठवण केली आहे आणि जिना पुढील सार्वजनिक भाग व मागील खाजगी भाग यांमध्ये विभाजक म्हणून काम करतो.",
                'cover_image' => 'images/site/portfolio/gangmills.jpg',
                'sort_order' => 5,
            ],
            [
                'slug' => 'coastal-retreat',
                'title' => 'Coastal Retreat',
                'title_mr' => 'किनारपट्टीवरील निवांत घर',
                'category' => 'modern-homes',
                'location' => 'Concept study · Konkan coast',
                'location_mr' => 'संकल्पना अभ्यास · कोकण किनारपट्टी',
                'summary' => 'A low, wide house planned for humid coastal weather, with shaded decks and cross-ventilated rooms.',
                'summary_mr' => 'दमट किनारपट्टीच्या हवामानासाठी नियोजित, सावलीचे डेक व आडव्या हवेसह रुंद व सपाट घर.',
                'description' => "Humidity and salt air shape this design more than the view does. The roof is low and wide, shading deep decks that can be used through most of the year.\n\nMaterials are chosen for coastal conditions: a raised plinth, easy-to-maintain plaster, and openings that can be fully opened for cross ventilation.",
                'description_mr' => "या रचनेला दृश्यापेक्षा दमट हवामान व क्षारयुक्त हवेने जास्त आकार दिला आहे. छत सपाट व रुंद असून खोल डेक्सना सावली देते, जे वर्षभराच्या बहुतांश काळात वापरता येतात.\n\nसाहित्य किनारपट्टीच्या परिस्थितीनुसार निवडले आहे: उंच पाया, सांभाळायला सोपे प्लास्टर आणि पूर्ण उघडता येणाऱ्या खिडक्या.",
                'cover_image' => 'images/site/portfolio/westcoast.jpg',
                'sort_order' => 6,
            ],
        ];

        foreach ($projects as $project) {
            PortfolioProject::query()->updateOrCreate(
                ['slug' => $project['slug']],
                [
                    ...$project,
                    'year' => null,
                    'gallery' => null,
                    'is_sample' => true,
                    'is_published' => true,
                ],
            );
        }
    }
}
