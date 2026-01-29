// Ethiopian Location Data
const ethiopianLocations = {
    addis_ababa: {
        name: "Addis Ababa",
        zones: ["Addis Ketema", "Akaki Kaliti", "Arada", "Bole", "Gulele", "Kirkos", "Kolfe Keranio", "Lideta", "Nifas Silk-Lafto", "Yeka"]
    },
    afar: {
        name: "Afar",
        zones: ["Awsi Rasu", "Bagido", "Duro", "Gablalu", "Kilbati", "Mille", "Rama", "Telalak", "Yallo"]
    },
    amhara: {
        name: "Amhara",
        zones: ["Agew Awi", "Bahir Dar", "Debub Gondar", "East Gojjam", "Metekel", "North Gondar", "North Shewa", "South Gondar", "West Gojjam", "Waghimra"]
    },
    benishangul_gumuz: {
        name: "Benishangul-Gumuz",
        zones: ["Asossa", "Kamashi", "Metekel", "Sirba Abay"]
    },
    dire_dawa: {
        name: "Dire Dawa",
        zones: ["Dire Dawa", "Gurgura"]
    },
    gambela: {
        name: "Gambela",
        zones: ["Akobo", "Gambela", "Godere", "Lare", "Wantoat"]
    },
    harari: {
        name: "Harari",
        zones: ["Dire Teyara", "Erer", "Gadam", "Sofi"]
    },
    oromia: {
        name: "Oromia",
        zones: ["Adama", "Arsi", "Bale", "Bora", "East Hararghe", "East Shewa", "Guji", "Horo Gudru", "Ilu Aba Bora", "Jimma", "Kellem Wollega", "North Shewa", "South West Shewa", "West Arsi", "West Hararghe", "West Shewa"]
    },
    sidama: {
        name: "Sidama",
        zones: ["Aleta Wondo", "Dara", "Hawassa", "Hula", "Lemo", "Misha", "Shebedino", "Wondo Genet"]
    },
    somali: {
        name: "Somali",
        zones: ["Afder", "Gode", "Jijig", "Korahe", "Liben", "Nogob", "Shabelle"]
    },
    south_ethiopia: {
        name: "South Ethiopia",
        zones: ["Alaba", "Amaro", "Basketo", "Basketo", "Boreda", "Derashe", "Gamo", "Gofa", "Halaba", "Konta", "Kucha", "Mareko", "Ribo", "Wolkite", "Yem"]
    },
    south_west_ethiopia: {
        name: "South West Ethiopia",
        zones: ["Bench Sheko", "Dawro", "Kafa", "Mizan Teferi", "Sheka", "Worabe"]
    },
    central_ethiopia: {
        name: "Central Ethiopia",
        zones: ["Abay Chomen", "Amuru", "Anfillo", "Dano", "Diga", "Gimbichu", "Horo", "Jibat", "Meta Robi", "Tole"]
    },
    tigray: {
        name: "Tigray",
        zones: ["Central Tigray", "Eastern Tigray", "North Western Tigray", "Southern Tigray", "Western Tigray"]
    },
    other: {
        name: "Other",
        zones: ["Other"]
    }
};

const woredas = {
    // Sample woredas for major regions
    addis_ababa: ["Addis Ketema", "Akaki Kaliti", "Arada", "Bole", "Gulele", "Kirkos", "Kolfe Keranio", "Lideta", "Nifas Silk-Lafto", "Yeka"],
    amhara: ["Bahir Dar", "Gonder", "Dessie", "Woldia", "Kombolcha", "Lalibela", "Debre Birhan", "Debre Markos", "Mekelle", "Adwa"],
    oromia: ["Adama", "Bishoftu", "Jimma", "Metu", "Nekemte", "Shashemene", "Woliso", "Ambo", "Goba", "Mek'i"],
    tigray: ["Mekelle", "Adwa", "Axum", "Adigrat", "Humera", "Shire", "Kola Tembien", "Wukro", "Saesi Tsaedaiba", "Irob"],
    sidama: ["Hawassa", "Aleta Wondo", "Dara", "Hula", "Lemo", "Misha", "Shebedino", "Wondo Genet", "Aroresa", "Boricha"],
    south_ethiopia: ["Arba Minch", "Sodo", "Bako", "Wolkite", "Tepi", "Mizan Teferi", "Bonga", "Dilla", "Hosaena", "Worabe"],
    somali: ["Gode", "Jijiga", "Degua Tembien", "Kebri Beyah", "Kebri Dahar", "Fafan", "Shabelle", "Afder", "Liben", "Korahe"],
    afar: ["Awash", "Gala Duf", "Bati", "Mille", "Rama", "Buramino", "Duber", "Harang", "Telalak", "Yalo"],
    benishangul_gumuz: ["Asossa", "Bambasi", "Dembi Dolo", "Komosso", "Oda Buldigilu", "Menge", "Sama", "Sirba", "Wemberima", "Bullen"],
    gambela: ["Gambela", "Abobo", "Akobo", "Dimma", "Gog", "Itang", "Jor", "Lare", "Mekelle", "Wantaat"],
    harari: ["Harar", "Dire Teyara", "Erer", "Gadam", "Sofi", "Hundene", "Kabele", "Sefer", "Shenkor", "Toger"],
    dire_dawa: ["Dire Dawa", "Gurgura", "Aboke", "Babile", "Kebri Beyah", "Mogadishu", "Wajale", "Warder", "Yoc", "Aysha"],
    south_west_ethiopia: ["Bonga", "Dawro", "Kafa", "Mizan Teferi", "Sheka", "Tepi", "Worabe", "Dekan", "Ghibe", "Masha"],
    central_ethiopia: ["Amuru", "Bako", "Dano", "Diga", "Gimbichu", "Horo", "Jibat", "Meta Robi", "Tole", "Wolema"]
};

const kebeles = {
    // Sample kebeles for major cities
    addis_ababa: {
        "Addis Ketema": ["Kebele 01", "Kebele 02", "Kebele 03", "Kebele 04", "Kebele 05", "Kebele 06", "Kebele 07", "Kebele 08", "Kebele 09", "Kebele 10"],
        "Arada": ["Kebele 01", "Kebele 02", "Kebele 03", "Kebele 04", "Kebele 05", "Kebele 06", "Kebele 07", "Kebele 08", "Kebele 09", "Kebele 10"],
        "Bole": ["Kebele 01", "Kebele 02", "Kebele 03", "Kebele 04", "Kebele 05", "Kebele 06", "Kebele 07", "Kebele 08", "Kebele 09", "Kebele 10"],
        "Kirkos": ["Kebele 01", "Kebele 02", "Kebele 03", "Kebele 04", "Kebele 05", "Kebele 06", "Kebele 07", "Kebele 08", "Kebele 09", "Kebele 10"],
        "Yeka": ["Kebele 01", "Kebele 02", "Kebele 03", "Kebele 04", "Kebele 05", "Kebele 06", "Kebele 07", "Kebele 08", "Kebele 09", "Kebele 10"]
    },
    default: {
        "Woreda 1": ["Kebele 01", "Kebele 02", "Kebele 03", "Kebele 04", "Kebele 05", "Kebele 06", "Kebele 07", "Kebele 08", "Kebele 09", "Kebele 10"],
        "Woreda 2": ["Kebele 01", "Kebele 02", "Kebele 03", "Kebele 04", "Kebele 05", "Kebele 06", "Kebele 07", "Kebele 08", "Kebele 09", "Kebele 10"],
        "Woreda 3": ["Kebele 01", "Kebele 02", "Kebele 03", "Kebele 04", "Kebele 05", "Kebele 06", "Kebele 07", "Kebele 08", "Kebele 09", "Kebele 10"]
    }
};

function loadZones() {
    const regionSelect = document.getElementById('region');
    const zoneSelect = document.getElementById('zone');
    const woredaSelect = document.getElementById('woreda');
    const kebeleSelect = document.getElementById('kebele');

    if (!regionSelect || !zoneSelect) return;

    const region = regionSelect.value;
    zoneSelect.innerHTML = '<option value="">Select Zone</option>';
    woredaSelect.innerHTML = '<option value="">Select Woreda</option>';
    kebeleSelect.innerHTML = '<option value="">Select Kebele</option>';

    if (ethiopianLocations[region]) {
        const zones = ethiopianLocations[region].zones;
        zones.forEach(zone => {
            const option = document.createElement('option');
            option.value = zone;
            option.textContent = zone;
            zoneSelect.appendChild(option);
        });
    }
}

function loadWoredas() {
    const zoneSelect = document.getElementById('zone');
    const woredaSelect = document.getElementById('woreda');
    const kebeleSelect = document.getElementById('kebele');

    if (!zoneSelect || !woredaSelect) return;

    const zone = zoneSelect.value;
    woredaSelect.innerHTML = '<option value="">Select Woreda</option>';
    kebeleSelect.innerHTML = '<option value="">Select Kebele</option>';

    if (woredas[zone]) {
        woredas[zone].forEach(woreda => {
            const option = document.createElement('option');
            option.value = woreda;
            option.textContent = woreda;
            woredaSelect.appendChild(option);
        });
    } else {
        const defaultWoredas = ["Woreda 1", "Woreda 2", "Woreda 3", "Woreda 4", "Woreda 5"];
        defaultWoredas.forEach(woreda => {
            const option = document.createElement('option');
            option.value = woreda;
            option.textContent = woreda;
            woredaSelect.appendChild(option);
        });
    }
}

function loadKebeles() {
    const woredaSelect = document.getElementById('woreda');
    const kebeleSelect = document.getElementById('kebele');

    if (!woredaSelect || !kebeleSelect) return;

    const woreda = woredaSelect.value;
    kebeleSelect.innerHTML = '<option value="">Select Kebele</option>';

    const defaultKebeles = ["Kebele 01", "Kebele 02", "Kebele 03", "Kebele 04", "Kebele 05", "Kebele 06", "Kebele 07", "Kebele 08", "Kebele 09", "Kebele 10"];
    defaultKebeles.forEach(kebele => {
        const option = document.createElement('option');
        option.value = kebele;
        option.textContent = kebele;
        kebeleSelect.appendChild(option);
    });
}
