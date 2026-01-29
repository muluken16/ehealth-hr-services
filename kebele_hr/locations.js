const ethiopiaData = {
    "addis_ababa": {
        "name": "Addis Ababa",
        "zones": {
            "Addis Ababa": ["Addis Ketema", "Akaki Kality", "Arada", "Bole", "Gullele", "Kirkos", "Kolfe Keranio", "Lideta", "Nifas Silk-Lafto", "Yeka", "Lemi Kura"]
        }
    },
    "afar": {
        "name": "Afar",
        "zones": {
            "Awsi Rasu (Zone 1)": ["Asayita", "Dubti", "Afambo", "Kurubari", "Mille", "Chifra", "Adar", "Elidar"],
            "Kilbet Rasu (Zone 2)": ["Abala", "Berhale", "Koneba", "Dallol", "Erebti", "Megale"],
            "Gabi Rasu (Zone 3)": ["Amibara", "Awash Fentale", "Bure Mudaytu", "Dulecha", "Geweane", "Argobba"],
            "Fantena Rasu (Zone 4)": ["Aura", "Ewa", "Gulina", "Teru", "Yalo"],
            "Hari Rasu (Zone 5)": ["Dawe", "Dewe", "Hadale Ele", "Sumurobi", "Telalak"]
        }
    },
    "amhara": {
        "name": "Amhara",
        "zones": {
            "North Gondar": ["Debark", "Addi Arkay", "Beles", "Jan Amora", "Tsegede", "Metema", "Qwara", "Tadachhik", "Lay Armachiho", "Dabat", "Wogera", "Addis Zemen"],
            "South Gondar": ["Debre Tabor", "Dera", "Fogera", "Libo Kemkem", "Farta", "Ebinat", "Kebbete", "Tach Gayint", "Simada", "Tendeye", "Mersa", "Guna"],
            "North Wollo": ["Woldiya", "Kobo", "Guba Lafto", "Habru", "Lalibela", "Gudebuk", "Kolla Duba", "Wirges", "Gemechis", "Delanta", "Bugna", "Goloftu"],
            "South Wollo": ["Dessie", "Kombolcha", "Wegera", "Batu", "Tenta", "Sirinka", "Kutaber", "Amba Sina", "Mekdela", "Tallay Adishihu", "Were Babo", "Legambo"],
            "East Gojjam": ["Debre Markos", "Machakel", "Gozamin", "Debre Sina", "Aneded", "Basoliben", "Sinan", "Bichena", "Enar Ena Enor", "Debuye", "Awabel", "Hulet Eju Ener"],
            "West Gojjam": ["Bahir Dar", "Mecha", "Yilmanadensa", "Gondar Zuria", "Dembia", "Kalu", "Lomidaro", "Bahir Dar Zuria", "Saris", "Fitina", "Achefer", "Zelimoye"],
            "Wag Hemra": ["Sekota", "Zaquala", "Dehana", "Abergele", "Adis Zemen", "Gishe", "Kuwagade", "Kobbo", "Lay Gayint", "Tarmaber", "Enebse Sar Meder"],
            "Awi": ["Injibara", "Dangila", "Chagni", "Banja", "Fagita Lekoma", "Guagusa Shekudad", "Kuisi", "Jawi", "Quara", "Dangila Zuria", "Zegde"],
            "Oromia Special": ["Kemissie", "Artuma Fursi", "Bati", "Basso Liben", "Mora", "Goba", "Qecha", "Werti", "Saddiso", "Bekoji", "Mena"],
            "North Shewa (Amhara)": ["Debre Berhan", "Ankober", "Efratana Gidim", "Mera", "Bahar Dar", "Shoa Robit", "Ketu Dere", "Sela Dengay", "Mekane Selam", "Gera Key", "Chacha"]
        }
    },
    "oromia": {
        "name": "Oromia",
        "zones": {
            "East Shewa": ["Adama", "Bishoftu", "Mojo", "Dugda", "Bole", "Hetoshe", "Gimbichu", "Dera", "Akaki", "Kaliti"],
            "West Shewa": ["Ambo", "Dendi", "Ejere", "Tole", "Jibat", "Wolkite", "Goro", "Sodo", "Bako", "Cheliya"],
            "North Shewa (Oromia)": ["Fiche", "Degem", "Wara Jarso", "Kimbibit", "Mulona Sululta", "Jida", "Mulo", "Yaya Gulele", "Haro", "Dukem"],
            "South West Shewa": ["Woliso", "Becho", "Tulu Bolo", "Goro", "Sapr Nenko", "Bako Tibe", "Sayo", "Gimbi", "Hawa", "Bore"],
            "Jimma": ["Jimma", "Limmu Seka", "Mana", "Gera", "Seka", "Qersa", "Omo Nada", "Dedo", "Kersa", "Tiro Afeta"],
            "Illubabor": ["Mettu", "Ale", "Darimu", "Bede", "Alge", "Yem", "Chorora", "Heto", "Wakabo", "Buno Bedelle"],
            "Bale": ["Robe", "Agarfa", "Sinana", "Goba", "Dinsho", "Mennana", "Goro", "Nensebo", "Dene", "Baddeno"],
            "Borena": ["Yabello", "Teltele", "Dire", "Moyale", "Bule Hora", "Arero", "Dugda Daga", "Bokko", "Dhas", "Guchi"],
            "Guji": ["Negele Borana", "Adola", "Shakiso", "Liben", "Dima", "Goro Dola", "Bore", "Odo Shakiso", "Sabo", "Hula"],
            "Arsi": ["Asella", "Tiyo", "Digeluna Tijo", "Ziway Dugda", "Bora", "Chole", "Hitosa", "Sude", "Gunndo", "Lemo"],
            "West Arsi": ["Shashemene", "Arsi Negele", "Kofele", "Wondo", "Hosaena", "Gedeb", "Dodola", "Semen Arsi", "Nansebo", "Chilanko"],
            "East Hararghe": ["Harar", "Babile", "Gursum", "Komboshamii", "Goler", "Jarso", "Qeerqer", "Fadashi", "Chiro", "Meyu"],
            "West Hararghe": ["Chiro", "Bedessa", "Gelemso", "Mieso", "Doba", "Tulo", "Gira", "Arbore", "Micheta", "Hunduna"],
            "East Wollega": ["Nekemte", "Guto Gida", "Sibu Sire", "Leka Dulecha", "Diga", "Seyo", "Jimma Gidami", "Gida Kiremu", "Bako Tibe", "Wama"],
            "West Wollega": ["Gimbi", "Guliso", "Lalo Assabi", "Babile", "Yubdo", "Gawo Dale", "Heto", "Kiltu Kara", "Babo", "Dene"],
            "Horo Guduru Wollega": ["Shambu", "Jardega Jarte", "Wayu Tuka", "Gudaya", "Dendi", "Haru", "Boke", "Kachisena", "Holleta", "Qunquna"],
            "Qellem Wollega": ["Dembidolo", "Anfillo", "Gidami", "Komo", "Boji", "Dagi", "Nanno", "Darimu", "Qellem", "Wara"]
        }
    },
    "tigray": {
        "name": "Tigray",
        "zones": {
            "Central Tigray": ["Axum", "Adwa", "Abergele", "Ebinat", "Wukro", "Kola Tembien", "Ahferom", "Hintalo", "Wukro", "Gheralta"],
            "East Tigray": ["Adigrat", "Bizet", "Gulomahda", "Erafo", "Saesi Tsaeda", "Ganta Afeshum", "Ziway", "Keras Gize", "Hawzen", "Wukro"],
            "North West Tigray": ["Shire", "Sheraro", "Tahtay Adiyabo", "Kone", "Laelay Adiyabo", "Tahtay Koraro", "Tsegede", "Humera", "Welqait", "Tadachhik"],
            "South Tigray": ["Maichew", "Alamata", "Ofla", "Endamehoni", "Raya Azebo", "Alaje", "Enderta", "Dogua", "Hames", "Michew"],
            "South East Tigray": ["Mekelle", "Hintalo Wajirat", "Dogua Tembien", "Kolla Tembien", "Ahferom", "Adwa", "Axum", "Gheralta", "Saesi", "Tsegede"],
            "West Tigray": ["Humera", "Welkait", "Tsegede", "Tahtay Adiyabo", "Sheraro", "Kafta", "Kommiya", "Barak", "Tsemri", "Mekelle"]
        }
    },
    "somali": {
        "name": "Somali",
        "zones": {
            "Sitti": ["Adburashid", "Afdem", "Erer", "Mekelle", "Girawa", "Bokh", "Dembel", "Mulo", "Mire", "Garer"],
            "Fafan": ["Jijiga", "Babille", "Gursum", "Kebribeyah", "Awbere", "Waja", "Hurso", "Degua", "Gunagado", "Dohan"],
            "Jarar": ["Degahbur", "Aware", "Dhagax-Madow", "Gursum", "Barako", "Galadi", "Dhagax", "Madow", "Daga", "Bako"],
            "Nogob": ["Fiq", "Segeg", "Gerbo", "Mire", "Burdood", "Afasharky", "Dhuxun", "Arar", "Garac", "Wadajir"],
            "Dollo": ["Warder", "Boh", "Danot", "Galgala", "Lug", "Doh", "Barbari", "Goldogob", "Kelafo", "Shabeellaha"],
            "Korahe": ["Kebridahar", "Shilabo", "Debeweyin", "Eyl", "Balan", "Biyood", "Shabeellaha", "Hadh", "Galinsoor", "Shimbir"],
            "Shabelle": ["Gode", "Adadle", "East Imey", "Ferfer", "Dolo Bay", "Badan", "West Imey", "Dolo", "Madash", "Boh"],
            "Afder": ["Hargele", "Bare", "Dolobaye", "Wajale", "Doolo", "Gheralta", "Sabawanaag", "Ufurow", "Lughaya", "Liben"],
            "Liben": ["Filtu", "Dolo Odo", "Dekasuftu", "Sabawanaag", "Lughaya", "Filtu", "Dolo", "Bari", "Jiriban", "Gor"],
            "Erer": ["Fiq", "Laga-hida", "Salahad", "Derwan", "Kalabaydhac", "Boorame", "Dacal", "Carac", "Bokh", "Arar"]
        }
    },
    "benishangul_gumuz": {
        "name": "Benishangul-Gumuz",
        "zones": {
            "Asosa": ["Asosa", "Bambasi", "Khomosha", "Menge", "Bauro", "Komoshe", "Siri", "Badi", "Odad", "Ganji"],
            "Kamashi": ["Kamashi", "Agalo Mite", "Balo Jiganjoy", "Dangur", "Gesh", "Mekete", "Dangur", "Baba", "Sirba", "Kumruk"],
            "Metekel": ["Gilgel Beles", "Bulen", "Dangur", "Wembera", "Mandura", "Jawi", "Guba", "Sama", "Debub", "Gosh"]
        }
    },
    "gambela": {
        "name": "Gambela",
        "zones": {
            "Anywaa": ["Gambela", "Abobo", "Itang", "Gog", "Lare", "Dimma", "Azer", "Tergol", "Owen", "Alero"],
            "Nuer": ["Akobo", "Lare", "Wentua", "Jikaw", "Wanthoa", "Ming", "Batha", "Gog", "Pochala", "Mekelle"],
            "Majang": ["Mengesh", "Godere", "Sugum", "Merer", "Dima", "Koko", "Gog", "Doba", "Bach", "Chali"]
        }
    },
    "harari": {
        "name": "Harari",
        "zones": {
            "Harar": ["Harar City", "Amir-Nur", "Abadir", "Kole", "Hara", "Araran", "Sofie", "Jegol", "Sisai", "Seid"]
        }
    },
    "dire_dawa": {
        "name": "Dire Dawa",
        "zones": {
            "Dire Dawa": ["Dire Dawa City", "Gurgura", "Sabian", "Kebri Beyah", "Beyah", "Towen", "Wabe", "Laga", "Harar", "Qobo"]
        }
    },
    "sidama": {
        "name": "Sidama",
        "zones": {
            "Sidama": ["Hawassa", "Yirgalem", "Aleta Wendo", "Bensa", "Boricha", "Dalocha", "Loko Abaya", "Hula", "Aroressa", "Wensho"],
            "Dale": ["Yirgalem", "Aleta Chiko", "Chire", "Gedeb", "Dore", "Bensa", "Aroresa", "Bona", "Gorasa", "Wendo"],
            "Aleta Wendo": ["Aleta Wendo", "Kore", "Bossa", "Dona", "Haru", "Shebedino", "Lemo", "Daza", "Heto", "Bira"]
        }
    },
    "central_ethiopia": {
        "name": "Central Ethiopia",
        "zones": {
            "Gurage": ["Wolkite", "Butajira", "Agena", "Gombora", "Meskan", "Mareko", "Selti", "Gumer", "Enemor", "Kokir"],
            "Silte": ["Worabe", "Lanfro", "Sankura", "Alicho", "Misrak", "Dalo", "Beto", "Gibe", "Silte", "Heto"],
            "Hadiya": ["Hossana", "Misha", "Lemo", "Gimbichu", "Wukro", "Shone", "Bade", "Kosha", "Gesuba", "Gunchire"],
            "Kembata Tembaro": ["Durame", "Angacha", "Doyogena", "Kedida", "Alba", "Mekoy", "Hadaro", "Tirunesh", "Beles", "Waja"],
            "Halaba": ["Halaba Kulito", "Kudo", "Chuko", "Wozeka", "Duna", "Borena", "Shone", "Mekane Selam", "Beko", "Meket"]
        }
    },
    "south_ethiopia": {
        "name": "South Ethiopia",
        "zones": {
            "Wolayta": ["Sodo", "Areka", "Boditi", "Mekkala", "Doyogena", "Kindo", "Alaba", "Gimbo", "Lemo", "Hosaena"],
            "Gamo": ["Arba Minch", "Chencha", "Mirab Abaya", "Bonke", "Geresse", "Dira", "Mele", "Demba", "Kucha", "Uba"],
            "Gofa": ["Sawla", "Bulki", "Demba", "Masha", "Bale", "Moye", "Bago", "Tarcha", "Buba", "Uba"],
            "Konso": ["Karat", "Konso", "Alero", "Fasha", "Garbicha", "Geleba", "Lemo", "Wote", "Miti", "Waja"],
            "South Omo": ["Jinka", "Turmi", "Hamer", "Bako", "Mursi", "Karo", "Gnangatom", "Dime", "Mekelle", "Kucha"],
            "Ari": ["Debre Tsehay", "Jinka", "Bako", "Mursi", "Turmi", "Hamer", "Karo", "Moyi", "Mela", "Gomma"],
            "Basketo": ["Laska", "Duna", "Gena", "Chamo", "Goba", "Dome", "Bona", "Lemo", "Misha", "Gofa"],
            "Burji": ["Soyama", "Bona", "Dara", "Heto", "Gofa", "Kucha", "Dima", "Gebeta", "Maya", "Gura"]
        }
    },
    "south_west_ethiopia": {
        "name": "South West Ethiopia",
        "zones": {
            "Keffa": ["Bonga", "Chenna", "Gimbo", "Gera", "Cheta", "Menji", "Sayo", "Kaka", "Tepi", "Masha"],
            "Sheka": ["Masha", "Anderacha", "Yeki", "Gembichu", "Chena", "Wushwush", "Gori", "Masha", "Chet", "Tepi"],
            "Bench Sheko": ["Mizan Teferi", "Siz", "Basketo", "Guraferda", "Dima", "Mewi", "Gofa", "Chena", "Gura", "Mizan"],
            "Dawro": ["Tarcha", "Tercha", "Loma", "Gena", "Bossa", "Isara", "Mareka", "Wesha", "Gemba", "Sayo"],
            "Konta": ["Ameya", "Konta", "Homo", "Saja", "Jajura", "Dembe", "Kose", "Goba", "Tseba", "Mare"],
            "West Omo": ["Jemu", "Gog", "Komo", "Majeng", "Dime", "Mekelle", "Togo", "Bako", "Gena", "Kucha"]
        }
    }
};

function loadZones() {
    const reg = document.getElementById('region').value;
    const z = document.getElementById('zone');
    const w = document.getElementById('woreda');
    const k = document.getElementById('kebele');

    z.innerHTML = '<option value="">Select Zone</option>';
    w.innerHTML = '<option value="">Select Woreda</option>';
    k.innerHTML = '<option value="">Select Kebele</option>';

    if (reg && ethiopiaData[reg]) {
        Object.keys(ethiopiaData[reg].zones).forEach(zoneName => {
            z.add(new Option(zoneName, zoneName));
        });
    }
}

function loadWoredas() {
    const reg = document.getElementById('region').value;
    const zn = document.getElementById('zone').value;
    const w = document.getElementById('woreda');
    const k = document.getElementById('kebele');

    w.innerHTML = '<option value="">Select Woreda</option>';
    k.innerHTML = '<option value="">Select Kebele</option>';

    if (reg && zn && ethiopiaData[reg] && ethiopiaData[reg].zones[zn]) {
        ethiopiaData[reg].zones[zn].forEach(woredaName => {
            w.add(new Option(woredaName, woredaName));
        });
    }
}

function loadKebeles() {
    const k = document.getElementById('kebele');
    k.innerHTML = '<option value="">Select Kebele</option>';
    // Since kebeles are too many, we use mock kebeles 1-20
    for (let i = 1; i <= 20; i++) {
        k.add(new Option('Kebele ' + i, 'Kebele ' + i));
    }
}
