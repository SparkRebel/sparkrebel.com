<?php

namespace PW\UserBundle\Command;

use PW\UserBundle\Document\User,
    Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/**
 * SeedBrandInfoCommand
 */
class SeedBrandInfoCommand extends ContainerAwareCommand
{
    /**
     * document manager placeholder instance
     */
    protected $dm;

    protected $info = array(
    "littlebird" => array (
    	 "hash" => "abac355adae436e7b03b6d4b271d3f0652dec4f5",
    	"about" => "LittleBird Philosophy\n
    If you don't own it...make it\n
    If it's you...rock it\n\n
    If you imagine it...create it\n
    If you dig it...live it\n
    If you don't know it...learn it",
    	"site" => "http://www.littlebirdstyle.com/"
    ),
    "gorjana" => array (
    	 "hash" => "e6e0eca6629e9487cf1c54f1a4a59d38b382afd3",
    	"about" => "Gorjana jewelry and handbags are designed to effortlessly collaborate with the chic, modern woman's wardrobe.",
    	"site" => "http://www.gorjana.com/"
    ),
    "Estee Lauder" => array (
    	 "hash" => "c30b86083a9b319f45609461c9f8e83ef83126f1",
    	"about" => "Estée Lauder is one of the world’s most renowned beauty companies. Our skincare, makeup and fragrance products are innovative, technologically advanced and proven effective.",
    	"site" => "http://www.esteelauder.com/index.tmpl"
    ),
    "Lancome" => array (
    	 "hash" => "1301141e8d08f8763d10318f298fb444909e5582",
    	"about" => "undefined",
    	"site" => "http://www.lancome-usa.com/"
    ),
    "Clinique" => array (
    	 "hash" => "d72eb12e0189b716d1722d518782afaef7f4fe61",
    	"about" => "undefined",
    	"site" => "http://www.clinique.com/index.tmpl"
    ),
    "Unique Vintage" => array (
    	 "hash" => "e93915b47af820ff3370c7379d0bf4dffdd7e05c",
    	"about" => "undefined",
    	"site" => "http://www.unique-vintage.com"
    ),
    "Zac Posen" => array (
    	 "hash" => "917d4614b1ed36a8bb903106c769db2eae50ce12",
    	"about" => "undefined",
    	"site" => "http://www.zacposen.com/"
    ),
    "Yves Saint Laurent" => array (
    	 "hash" => "03511c1f302e605232f0d8762815d04c0d0388d8",
    	"about" => "The exceptional legacy of Yves Saint Laurent has a contemporary identity  forged through innovative collections that marry elegance, refinement, French chic and timeless style.",
    	"site" => "http://www.ysl.com/en_US"
    ),
    "Revlon" => array (
    	 "hash" => "13a62766109e9c0bdcab5d0bc17e0749f90fe407",
    	"about" => "Revlon is a world leader in cosmetics, fragrance and personal care and is a leading mass market cosmetics brand.\n
    \n
    Vision: Glamour, Excitement, and Innovation through High-quality Products at Affordable Prices.",
    	"site" => "http://www.revlon.com/#/5"
    ),
    "Tory Burch" => array (
    	 "hash" => "c328c9e4a391118eb0ebe9970ef5d2170cb85b81",
    	"about" => "Tory Burch is an attainable, luxury lifestyle brand defined by classic American sportswear with an eclectic sensibility. It embodies the personal style and spirit of its CEO, Tory Burch.",
    	"site" => "http://www.toryburch.com"
    ),
    "Wet Seal" => array (
    	 "hash" => "8a19c5b93641ee4baaf0922ea65c6f7ac44c80b1",
    	"about" => "Wet Seal is a leading specialty retail brand offering current, wear-now fashion. The merchandise assortment, crossing several categories including apparel, accessories, shoes and home goods, is trendy, aspirational and sexy.",
    	"site" => "http://wetseal.com/home.jsp"
    ),
    "L'Oreal" => array (
    	 "hash" => "65cfb3a2b98ff5ed22538e39a3d6b630bb299cf2",
    	"about" => "Our signature phrase, “Because I’m Worth It”, is meant to inspire each and every woman to embrace her own unique beauty and reinforce her sense of self-worth.\n
    \n
    We believe that everyone should have the ability to express themselves. That’s why we are committed to creating the most groundbreaking, high-quality, yet inexpensive beauty products for all ages and ethnicities.",
    	"site" => "http://www.lorealparisusa.com/_us/_en/default.aspx"
    ),
    "Theory" => array (
    	 "hash" => "3e96732953d05ea77b143eaa595481da34c0cb38",
    	"about" => "Theory 1. Theory is a company, a concept, a philosophy, an aesthetic, a style, and a product 2. Theory is a phenomenon that spun out of great fitting pants 3. Theory is a brand that doesn't look to the future- Theory creates the future",
    	"site" => "http://www.theory.com/"
    ),
    "Sonia Rykiel" => array (
    	 "hash" => "9dc8d09194f559b2c6d8d314ba6dadc037b93374",
    	"about" => "Rykiel… Six letters that are as snappy as a slogan spelled out in strass. It is the emblem of a love story begun in the whirlwind of an extraordinary Spring, which continues to this day. In May 1968, Sonia Rykiel founded her label and opened a first boutique on the rue de Grenelle, in Paris.",
    	"site" => "http://soniarykiel.com/en.html"
    ),
    "Rachel Roy" => array (
    	 "hash" => "0933049be6c593d7f0f68b26c20bc0fcf199aad8",
    	"about" => "undefined",
    	"site" => "http://www.rachelroy.com/"
    ),
    "Ralph Lauren" => array (
    	 "hash" => "c8e07102fff99531c26ad3fee3a8f5ad8d1ce11e",
    	"about" => "Since 1967, Ralph Lauren has defined the essence of American style while elevating it to new heights of luxury. Striking a balance between “timeless” and “modern,” Ralph Lauren creates collections that express a unique sense of personal style inspired by the rich visual imagery around him: the rustic beauty of the American West, the golden age of Hollywood glamour, the sleek innovation of automotive design or an authentic equestrian heritage.",
    	"site" => "http://www.RalphLauren.com"
    ),
    "Max & Cleo" => array (
    	 "hash" => "80a5336a522b0973bdf0fb2c3655b5c0991469cb",
    	"about" => "undefined",
    	"site" => "http://www.maxandcleousa.com/"
    ),
    "Hanky Panky" => array (
    	 "hash" => "6b96f53d99757289863f009070cd5ac4919ddee3",
    	"about" => "Blending traditional with modern glam looks, Hanky Panky regularly appears in magazines and is a fashion favorite of countless celebrities.\n
    \n
    Hanky Panky continues its dedication to comfort, fit and quality. “Feel how comfortable sexy can be!®”",
    	"site" => "http://www.hankypanky.com/"
    ),
    "Elizabeth and James" => array (
    	 "hash" => "e1c236bca641c0563cfb3b5d4b633f51054d7e4b",
    	"about" => "Elizabeth and James is a modern lifestyle brand for a new generation. Co-designers Ashley Olsen and Mary-Kate Olsen seek to narrow the gap between designer and contemporary fashion. Encompassing women’s wear, shoes, jewelry and eyewear, Elizabeth and James embodies an eclectic lifestyle by blending uptown and downtown elements with casual and traditional styles.",
    	"site" => "http://elizabethandjames.us/"
    ),
    "Elie Tahari" => array (
    	 "hash" => "bf53eab481ebf7a390abc97c41abeef97981a630",
    	"about" => "Our mission is to produce clothing of the finest quality and attention to detail for customers looking for fashion and value in luxury ready-to-wear.",
    	"site" => "http://www.elietahari.com/"
    ),
    "Calvin Klein" => array (
    	 "hash" => "aaa8d6c3c524335381102df180c09e552a61faf5",
    	"about" => "undefined",
    	"site" => "http://www.calvinklein.com/home/index.jsp"
    ),
    "BCBGMAXAZRIA" => array (
    	 "hash" => "673c541f4c9a02318019ce780cc8e3649c3493c4",
    	"about" => "Always on the forefront of fashion, BCBGMAXAZRIA is the premier lifestyle collection for the modern woman. Reconciling creativity with accessibility and desirability with wearability, BCBGMAXAZRIA occupies a unique position in American fashion, offering sophisticated, confident designs that take consumers from work to weekend in style.",
    	"site" => "http://www.bcbg.com"
    ),
    "Shoshanna" => array (
    	 "hash" => "8f6b54fdd600ff9e97122595f2c2ffc6cc428ce1",
    	"about" => "Inspired by Shoshanna’s own frustration bikini-shopping for her shapelier figure, this revolutionary system accommodates all different body types and proportions.",
    	"site" => "http://shoshanna.com/"
    ),
    "French Connection" => array (
    	 "hash" => "b16f4f3e8772d5cddc6290c72c98395ee2676a39",
    	"about" => "Founded in 1972 by Stephen Marks, French Connection set out to create well-designed fashionable clothing that appealed to a broad market.",
    	"site" => "http://usa.frenchconnection.com/index.aspx?mscsmigrated=true"
    ),
    "Rampage" => array (
    	 "hash" => "9c1a6701dc5136a52ff239b94ebdce4a541b621d",
    	"about" => "Fashion-forward young women have chosen the Rampage brand to fill their wardrobes, keeping them looking sexy while remaining price-conscious. Rampage appeals to the women who can go from work to the club, as it successfully straddles the line between both work-appropriate and nightclub-ready apparel.",
    	"site" => "http://rampage.com/"
    ),
    "The North Face" => array (
    	 "hash" => "7c58fcf7b7ff08bafafa513af48f9d9d9632e810",
    	"about" => "Never stop exploring. We are named for the coldest, most unforgiving side of a mountain. We have helped explorers reach the most unfathomable heights of the Himalayas. But The North Face® legend begins, ironically, on a beach. More precisely, San Francisco's North Beach neighborhood, at an altitude of only 150 feet above sea level. It was here in 1966 that two hiking enthusiasts resolved to follow their passions and founded a small mountaineering retail store.",
    	"site" => "http://www.thenorthface.com/en_US/"
    ),
    "Betsey Johnson" => array (
    	 "hash" => "c1da88e782f31d00c61264164a161183f2e3b75b",
    	"about" => "\"Making clothes involves what I like…color, pattern, shape and movement…I like the everyday process…the people, the pressure, the surprise of seeing the work come alive walking and dancing around on strangers. Like red lipstick on the mouth, my products wake up and brighten and bring the wearer to life…drawing attention to her beauty and specialness…her moods and movements…her dreams and fantasies.\" Betsey Johnson",
    	"site" => "http://www.betseyjohnson.com/home/index.jsp"
    ),
    "Citizens of Humanity" => array (
    	 "hash" => "7c59dc84e250b1208464b1d44346ab36764e2a9d",
    	"about" => "undefined",
    	"site" => "http://citizensofhumanity.com/"
    ),
    "Torrid.com" => array (
    	 "hash" => "a86271bff476d4d6958caccd66f74041263ec89f",
    	"about" => "Our mission is to provide FASHION for our curvy fashionistas!  Torrid is the Destination for trendy plus-size fashion and accessories.",
    	"site" => "http://www.torrid.com/torrid/Homepage.jsp"
    ),
    "Lacoste" => array (
    	 "hash" => "7bceab14f03e687b5b82eb1b1ce821cb7af9b3dd",
    	"about" => "Lacoste is a lifestyle brand, born of the inventiveness of a tennis champion, René Lacoste, who created the first polo shirt ever, initially for himself and for his friends, to be both relaxed and elegant on and off the tennis courts.",
    	"site" => "http://www.lacoste.com/"
    ),
    "Hard Tail" => array (
    	 "hash" => "de92c18b18218528ba2871abba6aea22c6066e9f",
    	"about" => "Founded in Santa Monica, California, in 1991, by Dick Cantrell, this premium sportswear and denim fashion house stands out in the crowd because of its delicious color palette and signature design elements.  Hard Tail was conceived on the notion of premium tee-shirts with tattoo-inspired designs evoking that inner rockstar. The evolution of mobility, spirit and color had begun. In 2004, the company began manufacturing a high-end denim line built on the same tenants of quality, style and comfort—you really want to live in these jeans.",
    	"site" => "http://www.hardtailforever.com/"
    ),
    "Rebecca Minkoff" => array (
    	 "hash" => "6c801c0ecceef1cbef1aae11b16e3603082cf40d",
    	"about" => "An industry leader in casual luxury handbags, accessories, and apparel, Rebecca Minkoff’s playful and subtly edgy designs can be spotted on girls everywhere from downtown to uptown, in the US and abroad.",
    	"site" => "http://rebeccaminkoff.com/"
    ),
    "Adidas" => array (
    	 "hash" => "f33acb080c43f9cf87ee5cec1ae3a9953b9d1e9d",
    	"about" => "In 2012, the brand with the 3-Stripes shows its unwavering love for the game. From the World Cup pitches to the Olympic bobsleigh ice tracks, from the swagger of the sidewalk to the strut of the catwalk, from the NBA All Stars to the concert stages, adidas supports and encourages the love of the game – no matter the game.",
    	"site" => "http://www.adidas.com/us/homepage.asp"
    ),
    "Paige Denim" => array (
    	 "hash" => "c148656272218a197fe5541e33c42d6597c529ee",
    	"about" => "Known for its considered and unexpected details, Paige has grown into a denim powerhouse offering styles that reflect the brand’s sex appeal and passion for the craft. Paige uses only the finest fabrics and materials while each design is infused with thought out and considered elements. As the expert on fit, Paige promises that every style delivers a drop dead fit.",
    	"site" => "http://www.paigeusa.com/"
    ),
    "Wildfox" => array (
    	 "hash" => "d3e33ec72408bd8355f131bf50b4bd41de3f8089",
    	"about" => "Musician Jimmy Sommers and co-designers Kimberley Gordon and Emily Faulstich established Wildfox Couture in Los Angeles in 2007. Known for its soft fabrics and unique designs, the line has progressed into a full knit range of tops, bottoms, dresses, scarves, socks, and denim.",
    	"site" => "http://www.wildfoxcouture.com/"
    ),
    "Jessica Simpson" => array (
    	 "hash" => "f16020251436e6ea9cefab432b5555556c981a80",
    	"about" => "The collection is inspired and designed in collaboration with Jessica Simpson. The products celebrate her iconic, American image that is fashion-forward, accessible, comfortable and timeless\n
    \n
    American and feminine, forward but classically familiar, approachable yet aspirational, sexy yet sweet, flirtatious and whimsical, vintage at times, but always of the moment.",
    	"site" => "http://www.jessicasimpsoncollection.com/"
    ),
    "Nine West" => array (
    	 "hash" => "5453f4b5ebaa550c49d094fe256a9befda46f630",
    	"about" => "Nine West is a world-renowned fashion leader offering the must-have trends of the season. Affordable chic.",
    	"site" => "http://www.ninewest.com/"
    ),
    "Rebecca Taylor" => array (
    	 "hash" => "0710619b77b98000fff88e94b1c8a5f87d0dfef0",
    	"about" => "Rebecca Taylor is not just a lifestyle... but a state of mind.  Urban, feminine, bold, kittenish, modern, chic, sexy, cool... with a touch of sparkle. Rebecca Taylor's flirty, modern designs have inspired legions of loyal customers and celebrity clients to indulge their feminine wiles.",
    	"site" => "http://www.rebeccataylor.com/"
    ),
    "Nanette Lepore" => array (
    	 "hash" => "f795417a80f3cb2045945533a7292f5514ad3b62",
    	"about" => "Nanette Lepore - fashion designer known for chic and casual women's collections. since 1992.",
    	"site" => "http://www.nanettelepore.com/"
    ),
    "Michael Kors" => array (
    	 "hash" => "4162d9d71c035bfffb34fd5e75c7ac747624c6bf",
    	"about" => "Michael Kors is a world-renowned, award-winning designer of luxury accessories and ready to wear.",
    	"site" => "http://www.michaelkors.com/"
    ),
    "Cole Haan" => array (
    	 "hash" => "b1727f495a20e714023d3a28d05d11b06a98f3e1",
    	"about" => "Shaped by over a century of craftsmanship, innovation and style, the Cole Haan collection offers something for everyone: premium footwear, accessories and outerwear for men and women.",
    	"site" => "http://www.colehaan.com"
    ),
    "Chinese Laundry" => array (
    	 "hash" => "76bd7682365ce524d39825b7cc3dc6eb67992852",
    	"about" => "Chinese Laundry is the perfect expression of its signature combination of outstanding quality and value with a unique and inspired point of view. From stylish daytime looks in innovative fabrics and leathers to evening collections in eye-catching metallics and satins, Chinese Laundry consistently provides a mix of styles and trends that are ideal for today’s fashion landscape.",
    	"site" => "http://www.chineselaundry.com"
    ),
    "Rachel Zoe" => array (
    	 "hash" => "49f562500b52de55ede044c15b4cd7fadada6fab",
    	"about" => "undefined",
    	"site" => "http://www.thezoereport.com/"
    ),
    "Trina Turk" => array (
    	 "hash" => "6ace6390d77845212a13470b9e6ba9d9ec447977",
    	"about" => "Trina Turk is a lifestyle brand, inspired by the multicultural mix, architecture and landscape of Los Angeles and California. Trina's philosophy is to create wearable, optimistic fashion that incorporates the best aspects of classic American sportswear.",
    	"site" => "http://www.trinaturk.com/index.aspx"
    ),
    "Caparros" => array (
    	 "hash" => "1392554c39ccddf641e67acaa9fa3c1cf8e43d45",
    	"about" => "Caparros has built a reputation for designing unique, captivating dress shoes full of vibrant color and embellishment for everything from weddings and chic proms, to evening events and life-defining occasions. Along the way, Caparros has attracted a loyal following of women who appreciate styles that easily transition from day-into-nightlife.",
    	"site" => "http://www.caparrosshoes.com/home.aspx"
    ),
    "Ella Moss" => array (
    	 "hash" => "4eca7c8650a7640b661eeda0ae94e81d4b607d02",
    	"about" => "Known for bright color palettes, simple shapes, soft, wearable cottons and tonal stripes, Ella Moss strikes a delicate balance of being edgy and vintage, without being over-the-top trendy.",
    	"site" => "http://www.ellamoss.com/"
    ),
    "J Brand" => array (
    	 "hash" => "05a0e21c6ca65ba14099b7fd2d489b17c218e861",
    	"about" => "J BRAND set out to create classic and sophisticated jeans with the emphasis on fit — and one mandate: create timeless products.  Fashion leaders making a difference - we create style that excites your life.",
    	"site" => "http://www.jbrandjeans.com/"
    ),
    "Hot Topic" => array (
    	 "hash" => "ff4c220ce478a4dceaa573225fe252e04ee66c1d",
    	"about" => "At Hot Topic, you'll find a passion for music and pop culture in everything we do, and you'll see that our customers share that same passion.",
    	"site" => "http://www.hottopic.com/hottopic/Homepage.jsp"
    ),
    "Joie" => array (
    	 "hash" => "1f4f4a9cd89ccddd4e70c484be4508ae1380f0c2",
    	"about" => "The JOIE collection is understatedly chic. Constantly inspired by Southern California, its casual yet sophisticated way of life is translated directly into JOIE designs. The JOIE girl enjoys wearing casual, comfortable clothes accented by luxurious fabrics and details inspired by her travel throughout the world. Both modern and timeless in its appeal, the JOIE aesthetic draws its influence from vintage creations while successfully maintaining a fresh approach to fashion.",
    	"site" => "http://www.joie.com"
    ),
    "Juicy Couture" => array (
    	 "hash" => "2d8c3ac0e617796546716aee53f07bf6e8b10817",
    	"about" => "Juicy Couture is a glamorous, irreverent and fun lifestyle brand for the decidedly fashionable.  Known for ascending the track suit to its status as a casual luxury icon, Juicy Couture continues to evolve, bringing the same confident, whimsical and feminine attitude to all its designs.",
    	"site" => "http://www.juicycouture.com"
    ),
    "Frye" => array (
    	 "hash" => "1efa4e209ec73c889c0c0cf8a4d19a8342fcae8f",
    	"about" => "The Frye brand remains true to its heritage and vintage American roots with finely crafted, fashionable boots, shoes, and leather goods featuring rich leathers and quality hardware.",
    	"site" => "http://www.thefryecompany.com/"
    ),
    "Joe's Jeans" => array (
    	 "hash" => "a709b18469721c631cc4c0f34c4113cbf4904576",
    	"about" => "Celebrating over a decade as a leader in the premium denim market, Joe’s is best known for pioneering the concept of fits catered to specific body types. Joe’s innovative take on denim fits has garnered the brand a loyal fan following of women and celebrity fans alike.",
    	"site" => "http://www.joesjeans.com"
    ),
    "True Religion" => array (
    	 "hash" => "942aa13b968de20b345f62d4374d3375b2733800",
    	"about" => "True Religion offers a wide range of styles in nearly every category and embraces the motto: “It’s all about the fit.” True Religion clothing is made for and by people who don’t follow trends, they set them.",
    	"site" => "http://www.truereligionbrandjeans.com/"
    ),
    "Kate Spade" => array (
    	 "hash" => "424faee86b927bbf22b91cc9c214a558cfff31d4",
    	"about" => "Utility, wit and playful sophistication are the hallmarks of Kate Spade New York. As our world expands, our graceful, exuberant approach to the everyday is evident in every category we enter, from handbags and clothing to jewelry, shoes, stationery, glasses, baby and home.",
    	"site" => "http://www.katespade.com/"
    ),
    "Michael Stars" => array (
    	 "hash" => "a4ffb199dda03c10540dd3c2cac413777cf4a997",
    	"about" => "Los Angeles-based Michael Stars has come a long way since it founded the \"original tee\". The California-chic brand now offers contemporary women a complete collection of head-to-toe looks including: tees, casual sportswear, dresses, twill, linen, sweaters and accessories. From top fashion magazines to celebrity closets, Michael Stars is spreading \"Casual Luxury\" around the world.",
    	"site" => "http://www.michaelstars.com"
    ),
    "Splendid" => array (
    	 "hash" => "a1842fee6a1e1dc5e61043a3ded0444f8cea1e36",
    	"about" => "Founded in 2002, Splendid is the culmination of a ten year tireless search to find the softest fabric and most color absorbing yarns in the world in order to create the ultimate t-shirt.",
    	"site" => "http://www.splendid.com"
    ),
    "Steve Madden" => array (
    	 "hash" => "7a8a3af2f0adc00d314427085c39a4e6660939ba",
    	"about" => "Steve Madden's vision is to give young, fashion-forward women a unique way to express their individuality through style.",
    	"site" => "http://www.stevemadden.com/"
    ),
    "James Jeans" => array (
    	 "hash" => "8aa250f57af266937027a163b85f1207258c9b4f",
    	"about" => "Cult favorite denim for its most amazing fit with a strong focus on providing a fit that complements all body types and all ways of life.",
    	"site" => "http://www.jamesjeans.us/"
    ),
    "Lucky Brand" => array (
    	 "hash" => "bdfb4fece7706d43d3fdbb535545cf0cd0f821d0",
    	"about" => "We crafted our first jeans in Los Angeles in 1990 with this philosophy in mind. And we’ve been making vintage-inspired, great fitting denim for expressive, independent types ever since.",
    	"site" => "http://www.luckybrand.com"
    ),
    "Gap" => array (
    	 "hash" => "8230532c8c2fd99f0903ec4390b94a9718e2940c",
    	"about" => "Gap was founded in 1969 with a single store in San Francisco. Now we have thousands of stores across the world and we are committed to bringing you accessible style.",
    	"site" => "http://www.gap.com/"
    ),
    "Old Navy" => array (
    	 "hash" => "b24ddf78e50648da9c37f395e573b4844cf081f4",
    	"about" => "Old Navy brings serious fashion fun to the whole family. We’ve got it all, from trendy threads to barely-basic basics, for everyone from newborns to adults. At prices that are oh-so-right!",
    	"site" => "http://www.oldnavy.com"
    ),
    "Free People" => array (
    	 "hash" => "bde6ad8c449af05ef53b55a8e2e5f1b0edcaf96c",
    	"about" => "She is a free spirit...wakes up to see the sun shine.  Stays up to catch a falling star...she is our free people girl.  And she is the reason we do what we do.",
    	"site" => "http://www.freepeople.com/"
    ),
    "Dolce Vita" => array (
    	 "hash" => "f465d0a074c75c7f67b79a9fb57d6cc444394e4d",
    	"about" => "Dolce Vita's nonchalant attitude towards fashion resulted in footwear and clothing lines that are effortlessly stylish yet delightfully flirty.",
    	"site" => "http://www.dolcevita.com/"
    ),
    "American Eagle Outfitters" => array (
    	 "hash" => "efad132b264c01ea1b99cbc4084a6921517b9e54",
    	"about" => "American Eagle Outfitters is the crossroads where American prep meets current fashion. While our heritage is based in denim, we also offer other high-quality, on-trend clothing and accessories at affordable prices. American Eagle style is eternal. Live your life.",
    	"site" => "http://www.ae.com/web/index.jsp"
    ),
    "Nike" => array (
    	 "hash" => "157c47f78a863f4d3eca39baba6d0a8a4a428f91",
    	"about" => "Our Mission: To bring inspiration and innovation to every athlete in the world. If you have a body, you are an athlete.",
    	"site" => "http://store.nike.com/us/en_us/?sitesrc=uslp"
    ),
    "Vans" => array (
    	 "hash" => "42201ca1e9882959d75d4904b62f5d4a5829653e",
    	"about" => "Vans was founded in 1966 by Paul and James Van Doren, Serge Delia, and Gordon Lee. It has stood for authenticity in youth lifestyle, music and action sports since day one.",
    	"site" => "http://www.vans.com/"
    ),
    "dELiA*s" => array (
    	 "hash" => "d616563aca66d18e4fd7359b52cfa1541b7f52b2",
    	"about" => "dELiA*s offers hip fashion for the teen girl who loves to look pretty. We are the denim destination, known for having a perfect fit and offering the latest styles in tons of sizes and lengths. Our girl wants to be herself—genuine and upbeat—and we couldn’t agree more.",
    	"site" => "http://store.delias.com"
    ),
    "Aeropostale" => array (
    	 "hash" => "5bc3eeaf746f21fca1703f9dae6d05eab14df5e3",
    	"about" => "Aéropostale , Inc. is a mall-based, specialty retailer of casual apparel and accessories, principally targeting 14 to 17 year-old young women and men.",
    	"site" => "http://www.aeropostale.com/shop/index.jsp?categoryId=3534619"
    ),
    "Bebe" => array (
    	 "hash" => "7230540cbf5ea9e04e80722891d3a7932f715a54",
    	"about" => "Modern. Feminine. Sophisticated. With its distinct line of contemporary women’s apparel and accessories, bebe is the brand for women who seek trend-forward and expressive style. It’s for those who are defined by a confident and discerning attitude—and who seek fashion that mirrors this mindset.",
    	"site" => "http://www.bebe.com"
    ),
    "XOXO" => array (
    	 "hash" => "0bc235ed00d8ef683579caf8d99d224d80530727",
    	"about" => "XOXO sets the latest trends and is willing to take fashion risks in order to do so. XOXO appeals to women who are “in the know” and who seek runway looks to compliment their lifestyle at a compelling value.",
    	"site" => "http://www.xoxo.com/home.html"
    ),
    "Pacific Sunwear" => array (
    	 "hash" => "2d5731186616298cfefeec82e81f67c7ad049ee5",
    	"about" => "We started as a little surf shop in Newport Beach in 1980, and we're now one of the top names in teen fashion with over 800 stores in 50 states and Puerto Rico. As we've grown, our focus has remained: stay true to our roots in youth culture and offer what's next now.",
    	"site" => "http://shop.pacsun.com/home.jsp"
    )

    );


    /**
     * repo
     */
    protected $repo;

    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('seed:brand:info')
            ->setDescription('Give brand/merchant users our already-uploaded avatars, about and site data')
            ->setDefinition(array(
            ));
    }

    /**
     * execute
     *
     * @param InputInterface  $input  instance
     * @param OutputInterface $output instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->dm = $this->getContainer()
            ->get('doctrine_mongodb.odm.document_manager');

        $assetProvider = $this->getContainer()->get('pw.asset');
        $assetRepo = $this->dm->getRepository('PWAssetBundle:Asset');
        $userRepo = $this->dm->getRepository('PWUserBundle:User');

        $missingNames = $missingAssets = array();
        foreach ($this->info as $name => $details) {
            $hash = $details['hash'];
            $site = $details['site'];
            $about = $details['about'];

            $user = $userRepo->createQueryBuilder()
                ->field('name')->equals($name)
                ->getQuery()->execute()->getSingleResult();
            if (!$user) {
                $missingNames[] = $name;
                continue;
            }

            $asset = $assetRepo->findOneBy(array('hash' => $hash));
            if (!$asset) {
                $asset = $assetProvider->addImageFromUrl("http://i.plumwillow.com/assets/$hash.jpg");
                if (!$asset) {
                    $missingAssets[] = $name;
                    continue;
                }
                $asset->setUrl($asset->getSourceUrl());
                $this->dm->persist($asset);
            }

            $user->setIcon($asset);

            $user->setAbout($about);
            $user->setWebsiteUrl($site);

            $this->dm->persist($user);
            $output->write("\t$name\n");
        }
        $this->dm->flush();

        if ($missingNames) {
            $output->write("\nThe following users were not found\n");
            $output->write("\t" . implode($missingNames, "\n\t") . "\n");
        }

        if ($missingAssets) {
            $output->write("\nThe following had missing assets\n");
            $output->write("\t" . implode($missingAssets, "\n\t") . "\n");
        }
    }
}
