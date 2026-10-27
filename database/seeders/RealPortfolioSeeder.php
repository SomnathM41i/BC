<?php

namespace Database\Seeders;

use App\Models\PortfolioProject;
use Illuminate\Database\Seeder;

/**
 * Real project designs for the public portfolio.
 *
 * The images are 3D design visualisations, so each description says so
 * plainly. Records are keyed by slug, so re-running the seeder updates the
 * existing entries instead of creating duplicates.
 *
 * Run with:  php artisan db:seed --class=RealPortfolioSeeder
 */
class RealPortfolioSeeder extends Seeder
{
    public function run(): void
    {
        // The demo projects are retired; clears any copies left on a database
        // that was seeded before the real portfolio replaced them.
        PortfolioProject::query()->where('is_sample', true)->delete();

        $projects = [
            [
                'slug' => 'modern-duplex-residence',
                'title' => 'Modern Duplex Residence',
                'title_mr' => 'आधुनिक दुमजली निवास',
                'category' => 'modern-homes',
                'summary' => 'A two-storey home with a stone-clad base, warm wood-lined balconies and a deeply shaded entrance porch.',
                'summary_mr' => 'दगडी पाया, लाकडी बाल्कनी आणि सावली देणाऱ्या खोल प्रवेशद्वारासह दुमजली निवास.',
                'description' => "The front elevation is built from three simple elements: a dark stone plinth, a beige plaster body and a timber-lined balcony zone. Deep slab overhangs shade the glazing and keep the first floor cool through the day.\n\nThe entrance sits under a low porch, framed in wood, with the staircase placed behind it. Bedrooms open onto the front balcony, while the rear of the plan is kept private.\n\n3D design visualisation.",
                'description_mr' => "पुढील दर्शनी भाग तीन साध्या घटकांनी बनला आहे: गडद दगडी पाया, बेज रंगाचे प्लास्टर आणि लाकडी बाल्कनी. खोल छताचे वत्सरे खिडक्यांना सावली देतात आणि पहिला मजला दिवसभर थंड ठेवतात.\n\nप्रवेशद्वार लाकडी चौकटीतील सखल ओसरीखाली आहे; त्यामागे जिना आहे. शयनकक्ष पुढील बाल्कनीत उघडतात, तर मागील भाग खाजगी ठेवला आहे.\n\n3D Design सादरीकरण.",
                'cover_image' => 'images/site/portfolio/modern-duplex-residence/cover.jpg',
                'gallery' => [
                    'images/site/portfolio/modern-duplex-residence/01.jpg',
                    'images/site/portfolio/modern-duplex-residence/02.jpg',
                ],
                'sort_order' => 1,
            ],
            [
                'slug' => 'interior-design-collection',
                'title' => 'Interior Design Collection',
                'title_mr' => 'इंटीरियर Design संग्रह',
                'category' => 'interior-design',
                'summary' => 'Living, dining, kitchen, pooja and bedroom interiors developed in one warm, wood-led language.',
                'summary_mr' => 'बैठक, जेवण, स्वयंपाकघर, पूजा आणि शयनकक्ष — लाकडी सजावटीच्या एकाच उबदार भाषेत साकारलेले इंटीरियर.',
                'description' => "The rooms share a palette of warm timber, stone-grey surfaces and soft neutral upholstery. A carved peacock panel separates the entrance from the dining room, while the living room is organised around a double-height light well.\n\nThe kitchen uses handle-less shutters and open timber shelves. Bedrooms are quieter, with curved headboards and layered lighting.\n\n3D design visualisation.",
                'description_mr' => "सर्व खोल्यांमध्ये उबदार लाकूड, दगडी-राखाडी पृष्ठभाग आणि मऊ तटस्थ फर्निचर अशी एकच रंगसंगती आहे. कोरीव मोरांचा पॅनेल प्रवेशद्वाराला जेवणाच्या खोलीपासून वेगळे करतो; बैठक मात्र दुहेरी उंचीच्या प्रकाशझोताभोवती रचली आहे.\n\nस्वयंपाकघरात हँडलविरहित कपाटे आणि लाकडी खुल्या शेल्फ आहेत. शयनकक्ष शांत आहेत — वक्र हेडबोर्ड आणि थरांमध्ये दिलेला प्रकाश.\n\n3D Design सादरीकरण.",
                'cover_image' => 'images/site/portfolio/interior-design-collection/cover.jpg',
                'gallery' => [
                    'images/site/portfolio/interior-design-collection/01.jpg',
                    'images/site/portfolio/interior-design-collection/02.jpg',
                    'images/site/portfolio/interior-design-collection/03.jpg',
                    'images/site/portfolio/interior-design-collection/04.jpg',
                    'images/site/portfolio/interior-design-collection/05.jpg',
                    'images/site/portfolio/interior-design-collection/06.jpg',
                    'images/site/portfolio/interior-design-collection/07.jpg',
                    'images/site/portfolio/interior-design-collection/08.jpg',
                    'images/site/portfolio/interior-design-collection/09.jpg',
                    'images/site/portfolio/interior-design-collection/10.jpg',
                ],
                'sort_order' => 2,
            ],
            [
                'slug' => 'vijaylakshmi-residence',
                'title' => 'Vijaylakshmi Residence',
                'title_mr' => 'विजयलक्ष्मी निवास',
                'category' => 'residential',
                'summary' => 'A three-storey residence topped with a timber pavilion, with carved screens, a stone entrance and a lit name wall.',
                'summary_mr' => 'लाकडी पॅव्हेलियनने सजलेला तीन मजली निवास — कोरीव जाळ्या, दगडी प्रवेशद्वार आणि प्रकाशित नावाची भिंत.',
                'description' => "The house steps back at the top floor to form a covered terrace under a timber roof. Carved stone screens and a dark marble porch give the entrance a strong identity, while the upper balconies stay open to the street.\n\nThe name wall is built into the balcony parapet, so the signage reads as part of the architecture rather than an addition.\n\n3D design visualisation.",
                'description_mr' => "वरचा मजला मागे सरकून लाकडी छताखाली सावलीचा टेरेस तयार होतो. कोरीव दगडी जाळ्या आणि गडद संगमरवरी ओसरीमुळे प्रवेशद्वाराला वेगळी ओळख मिळते; वरच्या बाल्कन्या रस्त्याकडे खुल्या आहेत.\n\nनावाची भिंत बाल्कनीच्या कठड्यातच बांधली आहे, त्यामुळे साइनबोर्ड वेगळा न वाटता रचनेचा भाग वाटतो.\n\n3D Design सादरीकरण.",
                'cover_image' => 'images/site/portfolio/vijaylakshmi-residence/cover.jpg',
                'gallery' => [
                    'images/site/portfolio/vijaylakshmi-residence/01.jpg',
                ],
                'sort_order' => 3,
            ],
            [
                'slug' => 'terrace-residence',
                'title' => 'Terrace Residence',
                'title_mr' => 'टेरेस निवास',
                'category' => 'residential',
                'summary' => 'A large family home planned around a top-floor terrace pavilion, with a stone boundary wall and layered balconies.',
                'summary_mr' => 'वरच्या मजल्यावरील टेरेस पॅव्हेलियनभोवती नियोजित मोठे कौटुंबिक घर; दगडी संरक्षक भिंत आणि थरांमध्ये विभागलेल्या बाल्कन्या.',
                'description' => "Living spaces are stacked over three levels, and each floor opens to a different balcony. The top level sits back under a broad timber roof, making a shaded terrace for family gatherings.\n\nA patterned stone wall runs along the street, giving the house privacy without closing it off.\n\n3D design visualisation.",
                'description_mr' => "राहण्याच्या जागा तीन पातळ्यांवर रचल्या आहेत आणि प्रत्येक मजला वेगळ्या बाल्कनीत उघडतो. वरचा मजला रुंद लाकडी छताखाली मागे बसतो, त्यामुळे कुटुंबाच्या एकत्र येण्यासाठी सावलीचा टेरेस तयार होतो.\n\nरस्त्यालगत नमुन्याची दगडी भिंत आहे, जी घर बंद न करता गोपनीयता देते.\n\n3D Design सादरीकरण.",
                'cover_image' => 'images/site/portfolio/terrace-residence/cover.jpg',
                'gallery' => [
                    'images/site/portfolio/terrace-residence/01.jpg',
                    'images/site/portfolio/terrace-residence/02.jpg',
                ],
                'sort_order' => 4,
            ],
        ];

        foreach ($projects as $project) {
            PortfolioProject::query()->updateOrCreate(
                ['slug' => $project['slug']],
                [
                    ...$project,
                    'location' => null,
                    'location_mr' => null,
                    'year' => null,
                    'is_sample' => false,
                    'is_published' => true,
                ],
            );
        }
    }
}
