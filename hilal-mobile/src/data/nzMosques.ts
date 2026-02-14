/**
 * New Zealand Mosques Data
 * Comprehensive list of mosques across New Zealand
 * Source: iman.co.nz, my-masjid.com
 */

export interface Mosque {
  id: string;
  name: string;
  nameAr: string;
  address: string;
  city: string;
  region: string;
  lat: number;
  lng: number;
  phone?: string;
  email?: string;
  website?: string;
  myMasjidId?: string; // For mosques integrated with my-masjid.com
  hasIqamaTimes?: boolean;
}

export const NZ_MOSQUES: Mosque[] = [
  // WHANGAREI
  {
    id: 'whangarei-islamic',
    name: 'Northland Muslim Community Islamic Centre',
    nameAr: 'المركز الإسلامي لمجتمع المسلمين في نورثلاند',
    address: '11C Porowini Avenue',
    city: 'Whangarei',
    region: 'Northland',
    lat: -35.7251,
    lng: 174.3237,
    phone: '021 864 832',
  },

  // AUCKLAND - Airport Area
  {
    id: 'maunatul-islam',
    name: 'Maunatul Islam New Zealand',
    nameAr: 'منوات الإسلام نيوزيلندا',
    address: '45 Thomas Road, Mangere',
    city: 'Auckland',
    region: 'Auckland',
    lat: -36.9679,
    lng: 174.7877,
    myMasjidId: 'aa874dd0-feeb-4bf2-93fa-f112ffc87ab6',
    hasIqamaTimes: true,
  },
  {
    id: 'al-maqtoum-airport',
    name: 'Al Maqtoum Airport Masjid',
    nameAr: 'مسجد آل مقتوم بالمطار',
    address: '91 Westney Road, Mangere',
    city: 'Auckland',
    region: 'Auckland',
    lat: -36.9717,
    lng: 174.7894,
  },

  // AUCKLAND - Central & Eastern
  {
    id: 'university-masjid-auckland',
    name: 'University of Auckland Masjid',
    nameAr: 'مسجد جامعة أوكلاند',
    address: '9 Mount Street',
    city: 'Auckland',
    region: 'Auckland',
    lat: -36.8509,
    lng: 174.7707,
  },
  {
    id: 'masjid-e-umar',
    name: 'Masjid e Umar',
    nameAr: 'مسجد عمر',
    address: '185-187 Stoddard Road, Mt Roskill',
    city: 'Auckland',
    region: 'Auckland',
    lat: -36.9151,
    lng: 174.7322,
    website: 'https://masjideumar.co.nz',
  },
  {
    id: 'al-manar-trust',
    name: 'Al Manar Islamic Trust',
    nameAr: 'وقف المنار الإسلامي',
    address: '70 Carr Road, Mt Roskill',
    city: 'Auckland',
    region: 'Auckland',
    lat: -36.9161,
    lng: 174.7268,
  },
  {
    id: 'lynfield-islamic',
    name: 'Lynfield Islamic Centre',
    nameAr: 'المركز الإسلامي لينفيلد',
    address: '143 White Swan Road, Mt Roskill',
    city: 'Auckland',
    region: 'Auckland',
    lat: -36.9258,
    lng: 174.7208,
  },
  {
    id: 'masjid-ayesha',
    name: 'Masjid Ayesha',
    nameAr: 'مسجد عائشة',
    address: '96 Maich Road, Manurewa',
    city: 'Auckland',
    region: 'Auckland',
    lat: -37.0188,
    lng: 174.8917,
    website: 'https://masjidayesha.co.nz',
  },
  {
    id: 'masjid-at-taqwa',
    name: 'Masjid At-Taqwa',
    nameAr: 'مسجد التقوى',
    address: '58 Grayson Avenue, Manukau',
    city: 'Auckland',
    region: 'Auckland',
    lat: -36.9933,
    lng: 174.8795,
    website: 'https://masjidattaqwa.co.nz',
  },
  {
    id: 'glen-innes-islamic',
    name: 'Glen Innes Islamic Centre',
    nameAr: 'المركز الإسلامي غلين إنيس',
    address: '127 Elstree Avenue, Glen Innes',
    city: 'Auckland',
    region: 'Auckland',
    lat: -36.8756,
    lng: 174.8551,
  },
  {
    id: 'masjid-abu-bakr',
    name: 'Masjid Abu Bakr Siddique',
    nameAr: 'مسجد أبو بكر الصديق',
    address: '5B Cortina Place, Pakuranga',
    city: 'Auckland',
    region: 'Auckland',
    lat: -36.8912,
    lng: 174.9033,
  },
  {
    id: 'ahlul-bayt-foundation',
    name: 'Islamic Ahlul Bayt Foundation',
    nameAr: 'مؤسسة أهل البيت الإسلامية',
    address: '27 Ben Lomond Crescent, Pakuranga',
    city: 'Auckland',
    region: 'Auckland',
    lat: -36.8891,
    lng: 174.9003,
  },

  // AUCKLAND - South
  {
    id: 'al-mustafa-masjid',
    name: 'Al-Mustafa Jamia Masjid',
    nameAr: 'مسجد المصطفى الجامع',
    address: '26 Mangere Road, Otahuhu',
    city: 'Auckland',
    region: 'Auckland',
    lat: -36.9455,
    lng: 174.8327,
  },
  {
    id: 'al-farooq-cultural',
    name: 'Al Farooq Cultural Centre',
    nameAr: 'المركز الثقافي الفاروق',
    address: '34 Portage Road, Otahuhu',
    city: 'Auckland',
    region: 'Auckland',
    lat: -36.9423,
    lng: 174.8344,
  },
  {
    id: 'papatoetoe-islamic',
    name: 'Papatoetoe Islamic Centre',
    nameAr: 'المركز الإسلامي باباتويتوي',
    address: '63 Park Avenue, Papatoetoe',
    city: 'Auckland',
    region: 'Auckland',
    lat: -36.9748,
    lng: 174.8555,
  },
  {
    id: 'miyc-tawheed',
    name: 'Manukau Islamic Youth Centre (Masjid At-Tawheed)',
    nameAr: 'مركز شباب مانوكاو الإسلامي (مسجد التوحيد)',
    address: 'Great South Road, Manukau',
    city: 'Auckland',
    region: 'Auckland',
    lat: -36.9927,
    lng: 174.8788,
    website: 'https://miyc.co.nz',
  },

  // AUCKLAND - West
  {
    id: 'masjid-al-noor-avondale',
    name: 'Masjid Al Noor Avondale',
    nameAr: 'مسجد النور أفوندال',
    address: '122 Blockhouse Bay Road, Avondale',
    city: 'Auckland',
    region: 'Auckland',
    lat: -36.8988,
    lng: 174.6998,
  },
  {
    id: 'west-auckland-masjid',
    name: 'West Auckland Masjid',
    nameAr: 'مسجد غرب أوكلاند',
    address: '31-33 Armada Drive, Ranui',
    city: 'Auckland',
    region: 'Auckland',
    lat: -36.8577,
    lng: 174.6105,
  },
  {
    id: 'ponsonby-masjid',
    name: 'Ponsonby Masjid (NZMA)',
    nameAr: 'مسجد بونسونبي',
    address: '17 Vermont Street, Ponsonby',
    city: 'Auckland',
    region: 'Auckland',
    lat: -36.8527,
    lng: 174.7455,
  },
  {
    id: 'mt-albert-islamic',
    name: 'Mt Albert Islamic Centre',
    nameAr: 'المركز الإسلامي ماونت ألبرت',
    address: 'Rocket Park, New North Road',
    city: 'Auckland',
    region: 'Auckland',
    lat: -36.8844,
    lng: 174.7177,
  },
  {
    id: 'kelston-islamic',
    name: 'Kelston Islamic Centre',
    nameAr: 'المركز الإسلامي كيلستون',
    address: '45 Cartwright Road, Kelston',
    city: 'Auckland',
    region: 'Auckland',
    lat: -36.9073,
    lng: 174.6528,
  },
  {
    id: 'new-lynn-islamic',
    name: 'New Lynn Islamic Centre (Masjid Al-Fatah)',
    nameAr: 'المركز الإسلامي نيو لين (مسجد الفتح)',
    address: '13 Ward Street, New Lynn',
    city: 'Auckland',
    region: 'Auckland',
    lat: -36.9088,
    lng: 174.6839,
    website: 'https://masjidalfatah.co.nz',
  },

  // AUCKLAND - North Shore
  {
    id: 'north-shore-islamic',
    name: 'North Shore Islamic Centre',
    nameAr: 'المركز الإسلامي نورث شور',
    address: '9B Kaimahi Drive, Glenfield',
    city: 'Auckland',
    region: 'Auckland',
    lat: -36.7797,
    lng: 174.7227,
  },
  {
    id: 'birkenhead-islamic',
    name: 'Birkenhead Islamic Centre',
    nameAr: 'المركز الإسلامي بيركينهيد',
    address: '79 Onewa Road, Northcote',
    city: 'Auckland',
    region: 'Auckland',
    lat: -36.8044,
    lng: 174.7427,
  },

  // AUCKLAND - South Auckland
  {
    id: 'al-iqra-takanini',
    name: 'Al Iqra Islamic Centre',
    nameAr: 'المركز الإسلامي الإقراء',
    address: '12 Tironui Station Road, Takanini',
    city: 'Auckland',
    region: 'Auckland',
    lat: -37.0394,
    lng: 174.9044,
  },
  {
    id: 'pukekohe-islamic',
    name: 'Pukekohe Islamic Centre',
    nameAr: 'المركز الإسلامي بوكيكوهي',
    address: '107 Princess Street, Pukekohe',
    city: 'Pukekohe',
    region: 'Auckland',
    lat: -37.2044,
    lng: 174.9055,
  },

  // HAMILTON
  {
    id: 'jamii-masjid-hamilton',
    name: 'Jamii Masjid Hamilton',
    nameAr: 'مسجد جامعي هاملتون',
    address: '921 Heaphy Terrace',
    city: 'Hamilton',
    region: 'Waikato',
    lat: -37.7945,
    lng: 175.2795,
    website: 'https://www.waikatomuslims.org.nz',
  },
  {
    id: 'hamilton-west-islamic',
    name: 'Hamilton West Islamic Centre',
    nameAr: 'المركز الإسلامي غرب هاملتون',
    address: '45 Bandon Street, Frankton',
    city: 'Hamilton',
    region: 'Waikato',
    lat: -37.7877,
    lng: 175.2611,
  },
  {
    id: 'rototuna-islamic',
    name: 'Rototuna Islamic Centre (Masjid Khadija)',
    nameAr: 'المركز الإسلامي روتوتونا (مسجد خديجة)',
    address: '240 Thomas Road, Rototuna',
    city: 'Hamilton',
    region: 'Waikato',
    lat: -37.7433,
    lng: 175.2944,
  },

  // TAURANGA
  {
    id: 'tauranga-masjid',
    name: 'Tauranga Masjid',
    nameAr: 'مسجد تاورانغا',
    address: '85 18th Avenue',
    city: 'Tauranga',
    region: 'Bay of Plenty',
    lat: -37.6988,
    lng: 176.1477,
  },

  // ROTORUA
  {
    id: 'rotorua-islamic',
    name: 'Rotorua Islamic Centre',
    nameAr: 'المركز الإسلامي روتوروا',
    address: '21 Tarewu Road',
    city: 'Rotorua',
    region: 'Bay of Plenty',
    lat: -38.1368,
    lng: 176.2497,
  },

  // TARANAKI
  {
    id: 'new-plymouth-masjid',
    name: 'Muslim Association of Taranaki',
    nameAr: 'الجمعية الإسلامية تاراناكي',
    address: '9 Cracroft Street',
    city: 'New Plymouth',
    region: 'Taranaki',
    lat: -39.0556,
    lng: 174.0752,
    phone: '0800 786 000',
    website: 'https://nakimuslim.org',
  },
  {
    id: 'hawera-islamic',
    name: 'Hawera Islamic Centre',
    nameAr: 'المركز الإسلامي هاويرا',
    address: '20 Turuturu Road',
    city: 'Hawera',
    region: 'Taranaki',
    lat: -39.5905,
    lng: 174.2805,
  },

  // WHANGANUI
  {
    id: 'masjid-bilal-whanganui',
    name: 'Masjid-E-Bilal Whanganui',
    nameAr: 'مسجد بلال وانغانوي',
    address: '68 Talbot Street, Whanganui East',
    city: 'Whanganui',
    region: 'Manawatu-Wanganui',
    lat: -39.9297,
    lng: 175.0577,
  },

  // PALMERSTON NORTH
  {
    id: 'palmerston-north-islamic',
    name: 'Palmerston North Islamic Centre',
    nameAr: 'المركز الإسلامي بالمرستون نورث',
    address: '81 Cook Street',
    city: 'Palmerston North',
    region: 'Manawatu-Wanganui',
    lat: -40.3523,
    lng: 175.6082,
  },
  {
    id: 'massey-islamic',
    name: 'Massey University Islamic Centre',
    nameAr: 'المركز الإسلامي جامعة ماسي',
    address: 'Campus Road, Massey University',
    city: 'Palmerston North',
    region: 'Manawatu-Wanganui',
    lat: -40.3859,
    lng: 175.6177,
  },

  // HASTINGS / NAPIER
  {
    id: 'hawkes-bay-masjid',
    name: 'Hawkes Bay Baitul Mokarram Masjid',
    nameAr: 'مسجد بيت المكرم هوكس باي',
    address: '718 Heretaunga Street East',
    city: 'Hastings',
    region: 'Hawkes Bay',
    lat: -39.6377,
    lng: 176.8488,
    phone: '06-878 6001',
  },

  // WELLINGTON
  {
    id: 'wellington-masjid',
    name: 'Wellington Masjid (Kilbirnie)',
    nameAr: 'مسجد ويلنغتون (كيلبيرني)',
    address: '7-11 Queens Drive, Kilbirnie',
    city: 'Wellington',
    region: 'Wellington',
    lat: -41.3144,
    lng: 174.8055,
    phone: '04-387 4226',
    website: 'https://iman.org.nz',
  },
  {
    id: 'porirua-islamic',
    name: 'Porirua Islamic Centre',
    nameAr: 'المركز الإسلامي بوريروا',
    address: '58-60 Waihora Crescent, Waitangirua',
    city: 'Porirua',
    region: 'Wellington',
    lat: -41.1333,
    lng: 174.8412,
  },
  {
    id: 'hutt-valley-islamic',
    name: 'Hutt Valley Islamic Centre',
    nameAr: 'المركز الإسلامي هت فالي',
    address: '14-20 Hunter Street',
    city: 'Lower Hutt',
    region: 'Wellington',
    lat: -41.2097,
    lng: 174.9082,
  },
  {
    id: 'newlands-islamic',
    name: 'Newlands Islamic Centre',
    nameAr: 'المركز الإسلامي نيولاندز',
    address: '40 Bracken Road, Newlands',
    city: 'Wellington',
    region: 'Wellington',
    lat: -41.2275,
    lng: 174.8205,
  },

  // NELSON
  {
    id: 'nelson-islamic',
    name: 'Nelson Islamic Society',
    nameAr: 'الجمعية الإسلامية نيلسون',
    address: 'Nelson',
    city: 'Nelson',
    region: 'Nelson',
    lat: -41.2706,
    lng: 173.2840,
  },

  // BLENHEIM
  {
    id: 'marlborough-islamic',
    name: 'Muslim Association of Marlborough',
    nameAr: 'الجمعية الإسلامية مارلبورو',
    address: '25 Alfred Street, Marlborough Community Centre',
    city: 'Blenheim',
    region: 'Marlborough',
    lat: -41.5134,
    lng: 173.9612,
    phone: '027-353 2125',
  },

  // CHRISTCHURCH
  {
    id: 'masjid-al-noor-chch',
    name: 'Masjid Al Noor (Al-Noor Mosque)',
    nameAr: 'مسجد النور',
    address: '101 Deans Avenue, Riccarton',
    city: 'Christchurch',
    region: 'Canterbury',
    lat: -43.5347,
    lng: 172.6077,
    phone: '03-348 3930',
    website: 'https://macnz.org',
  },
  {
    id: 'linwood-islamic',
    name: 'Linwood Islamic Centre (Masjid Al-Shuhada)',
    nameAr: 'المركز الإسلامي لينوود (مسجد الشهداء)',
    address: '223 Linwood Avenue',
    city: 'Christchurch',
    region: 'Canterbury',
    lat: -43.5344,
    lng: 172.6788,
  },
  {
    id: 'hillmorton-islamic',
    name: 'Hillmorton/Spreydon Islamic Centre',
    nameAr: 'المركز الإسلامي هيلمورتون',
    address: '30 Lincoln Road, Hillmorton',
    city: 'Christchurch',
    region: 'Canterbury',
    lat: -43.5466,
    lng: 172.6044,
  },

  // ASHBURTON
  {
    id: 'ashburton-masjid',
    name: 'Ashburton Masjid',
    nameAr: 'مسجد أشبيرتون',
    address: '139 Archibald Street, Tinwald',
    city: 'Ashburton',
    region: 'Canterbury',
    lat: -43.9055,
    lng: 171.7577,
  },

  // DUNEDIN
  {
    id: 'masjid-al-huda',
    name: 'Masjid Al Huda',
    nameAr: 'مسجد الهدى',
    address: '21 Clyde Street',
    city: 'Dunedin',
    region: 'Otago',
    lat: -45.8744,
    lng: 170.5027,
    phone: '03-477 1838',
  },

  // QUEENSTOWN
  {
    id: 'queenstown-islamic',
    name: 'Queenstown Islamic Centre',
    nameAr: 'المركز الإسلامي كوينزتاون',
    address: 'Queenstown',
    city: 'Queenstown',
    region: 'Otago',
    lat: -45.0312,
    lng: 168.6626,
  },

  // INVERCARGILL
  {
    id: 'southland-masjid',
    name: 'Southland Muslim Association Community Centre',
    nameAr: 'مركز مجتمع جمعية المسلمين ساوثلاند',
    address: '31 Fairview Avenue',
    city: 'Invercargill',
    region: 'Southland',
    lat: -46.4132,
    lng: 168.3475,
    phone: '027 311 7962',
    website: 'https://sma.org.nz',
  },
];

// Get unique cities for dropdown
export const NZ_CITIES = [...new Set(NZ_MOSQUES.map((m) => m.city))].sort();

// Get unique regions for grouping
export const NZ_REGIONS = [...new Set(NZ_MOSQUES.map((m) => m.region))].sort();

// Get mosques by city
export const getMosquesByCity = (city: string): Mosque[] => {
  return NZ_MOSQUES.filter((m) => m.city === city);
};

// Get mosques by region
export const getMosquesByRegion = (region: string): Mosque[] => {
  return NZ_MOSQUES.filter((m) => m.region === region);
};

// Get mosque by ID
export const getMosqueById = (id: string): Mosque | undefined => {
  return NZ_MOSQUES.find((m) => m.id === id);
};

// Get mosques with my-masjid.com integration
export const getMyMasjidMosques = (): Mosque[] => {
  return NZ_MOSQUES.filter((m) => m.myMasjidId);
};
