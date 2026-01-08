<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'zone_hr') {
    header('Location: ../index.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthFirst | Zone HR Employees</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
    <div class="hr-container">
        <?php include 'sidebar.php'; ?>
        <?php
        $page_title = 'Employee Directory';
        include 'navbar.php';
        ?>
            <div class="hr-dashboard">
                <div class="filters-section">
                    <div class="search-box"><input type="text" placeholder="Search employees..." id="employeeSearch"><i class="fas fa-search"></i></div>
                    <select class="filter-select" id="departmentFilter"><option value="">All Departments</option><option value="medical">Medical</option><option value="admin">Administration</option><option value="technical">Technical</option><option value="support">Support</option></select>
                    <select class="filter-select" id="statusFilter"><option value="">All Status</option><option value="active">Active</option><option value="on-leave">On Leave</option><option value="inactive">Inactive</option></select>
                    <button class="add-btn" onclick="openModal('addEmployeeModal')"><i class="fas fa-plus"></i> Add Employee</button>
                </div>
                <div class="hr-section">
                    <div class="hr-section-header">
                        <h2 class="hr-section-title">Employee Directory</h2>
                        <div class="hr-section-actions">
                            <button class="section-action-btn" onclick="exportEmployees()">
                                <i class="fas fa-download"></i> Export
                            </button>
                            <button class="section-action-btn" onclick="refreshEmployees()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="hr-section-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Employee ID</th>
                                        <th>Department</th>
                                        <th>Position</th>
                                        <th>Join Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="employeeTableBody">
                                    <!-- Employee data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Employee Modal -->
    <div class="modal" id="addEmployeeModal">
        <div class="modal-content">
            <span class="close-modal" id="closeEmployeeModal">&times;</span>
            <h2 class="modal-title">Add New Employee</h2>

            <form id="employeeForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstName">First Name *</label>
                        <input type="text" id="firstName" name="firstName" required>
                    </div>
                    <div class="form-group">
                        <label for="middleName">Middle Name</label>
                        <input type="text" id="middleName" name="middleName">
                    </div>
                    <div class="form-group">
                        <label for="lastName">Last Name *</label>
                        <input type="text" id="lastName" name="lastName" required>
                    </div>
                </div>

                 <div class="form-group">
            <label for="newPatientGender">Gender</label>
            <select id="newPatientGender" name="gender" required>
                <option value="">Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
            </select>
         </div>


           
    <div class="form-group">
        <label for="dateOfBirth">Date of Birth *</label>
        <input type="date" id="dateOfBirth" name="dateOfBirth" required>
    </div>

    <div class="form-group">
        <label for="religion">Religion *</label>
        <select id="religion" name="religion" required>
            <option value="">Select Religion</option>
            <option value="christianity">Orthodox</option>
            <option value="islam">Islam</option>
            <option value="Protestant">Protestant</option>
            <option value="judaism">Judaism</option>
            <option value="hinduism">Hinduism</option>
            <option value="other">Other</option>
        </select>
    </div>


   

    

<div class="form-group">
    <label for="citizenship">Citizenship *</label>
    <select id="citizenship" name="citizenship" required onchange="checkOther(this)">
        <option value="">Select your country</option>
        <option value="Ethiopia">Ethiopia</option>
        <option value="United States">United States</option>
        <option value="United Kingdom">United Kingdom</option>
        <option value="Canada">Canada</option>
        <option value="Germany">Germany</option>
        <option value="France">France</option>
        <option value="India">India</option>
        <option value="China">China</option>
        <option value="Japan">Japan</option>
        <option value="Other">Other</option>
    </select>

    <!-- Input for "Other" country -->
    <input type="text" id="otherCitizenship" name="otherCitizenship" placeholder="Enter your country" style="display:none; margin-top:5px;">
</div>

<script>
function checkOther(select) {
    var otherInput = document.getElementById('otherCitizenship');
    if(select.value === "Other") {
        otherInput.style.display = "block";
        otherInput.required = true;
    } else {
        otherInput.style.display = "none";
        otherInput.required = false;
    }
}
</script>




<!DOCTYPE html>
<html>
<head>
    <title>Region Zone Woreda Selection</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        select {
            width: 100%;
            padding: 8px;
        }
    </style>
</head>



<body>

<div class="form-group">
    <label for="region">Region *</label>
    <select id="region" name="region" required onchange="loadZones()">
        <option value="">Select Region</option>
        <option value="Addis Ababa">Addis Ababa</option>
        <option value="Afar">Afar</option>
        <option value="Amhara">Amhara</option>
        <option value="Benishangul-Gumuz">Benishangul-Gumuz</option>
        <option value="Dire Dawa">Dire Dawa</option>
        <option value="Gambela">Gambela</option>
        <option value="Harari">Harari</option>
        <option value="Oromia">Oromia</option>
        <option value="Sidama">Sidama</option>
        <option value="Somali">Somali</option>
        <option value="South West Ethiopia">South West Ethiopia</option>
        <option value="Southern Nations">Southern Nations (SNNPR)</option>
        <option value="Tigray">Tigray</option>
        <option value="Other">Other</option>
    </select>
</div>

<div class="form-group">
    <label for="zone">Zone *</label>
    <select id="zone" name="zone" required onchange="loadWoredas()">
        <option value="">Select Zone</option>
    </select>
</div>

<div class="form-group">
    <label for="woreda">Woreda *</label>
    <select id="woreda" name="woreda" required onchange="loadKebeles()">
        <option value="">Select Woreda</option>
    </select>
</div>

<div class="form-group">
    <label for="kebele">Kebele *</label>
    <select id="kebele" name="kebele" required>
        <option value="">Select Kebele</option>
    </select>
</div>

<script>
// Comprehensive Ethiopian Location Data (Full Source)
const locationData = {
    "Afar": {
        "Administrative Zone 1 (Awsi Rasu)": ["Afambo", "Asayita", "Chifra", "Dubti", "Elidar", "Kori", "Mille", "Ada'ar"],
        "Administrative Zone 2 (Kilbet Rasu)": ["Abala", "Afdera", "Berhale", "Dallol", "Erebti", "Koneba", "Megale", "Bidu"],
        "Administrative Zone 3 (Gabi Rasu)": ["Amibara", "Awash Fentale", "Bure Mudaytu", "Dulecha", "Gewane"],
        "Administrative Zone 4 (Fantena Rasu)": ["Aura", "Ewa", "Gulina", "Teru", "Yalo"],
        "Administrative Zone 5 (Hari Rasu)": ["Dalifage", "Dewe", "Hadele Ele", "Simurobi Gele'alo", "Telalak"],
        "Argobba (special woreda)": ["Argobba"]
    },
    "Amhara": {
        "Agew Awi": ["Ankasha Guagusa", "Banja Shekudad", "Dangila", "Faggeta Lekoma", "Guagusa Shekudad", "Guangua", "Jawi", "Metekel", "Pawi"],
        "East Gojjam": ["Aneded", "Awabel", "Baso Liben", "Bibugn", "Debay Telatgen", "Debre Elias", "Debre Marqos (town)", "Dejen", "Enarj Enawga", "Enbise Sar Midir", "Enemay", "Goncha", "Goncha Siso Enese", "Guzamn", "Hulet Ej Enese", "Machakel", "Shebel Berenta", "Sinan"],
        "North Gondar": ["Addi Arkay", "Alefa", "Beyeda", "Chilga", "Dabat", "Debarq", "Dembiya", "Gondar (town)", "Gondar Zuria", "Jan Amora", "Kuara", "Lay Armachiho", "Metemma", "Mirab Armachiho", "Mirab Belessa", "Misraq Belessa", "Humera", "Tachi Armachiho"],
        "North Shewa": ["Angolalla Tera", "Ankober", "Antsokiyana Gemza", "Asagirt", "Basona Werana", "Berehet", "Debre Berhan (town)", "Efratana Gidim", "Ensaro", "Geshe", "Hagere Mariamna Kesem", "Kewet", "Menjarna Shenkora", "Menz Gera Midir", "Menz Keya Gebreal", "Menz Lalo Midir", "Menz Mam Midir", "Merhabiete", "Mida Woremo", "Mojana Wadera", "Moretna Jiru", "Siyadebrina Wayu", "Termaber"],
        "North Wollo": ["Bugna", "Dawunt", "Delanta", "Gidan", "Guba Lafto", "Habru", "Kobo", "Lasta", "Meket", "Wadla", "Weldiya (town)"],
        "Oromia": ["Artuma Fursi", "Bati", "Dawa Chefe", "Dawa Harewa", "Jile Timuga", "Kemise (town)"],
        "South Gondar": ["Debre Tabor (town)", "Dera", "Ebenat", "Farta", "Fogera", "Kemekem", "Lay Gayint", "Mirab Este", "Misraq Este", "Simada", "Tach Gayint"],
        "South Wollo": ["Abuko", "Amba Sel", "Borena", "Dessie (town)", "Dessie Zuria", "Jama", "Kalu (woreda)", "Kelala", "Kombolcha (town)", "Kutaber", "Legahida", "Legambo", "Magdala", "Mehal Sayint", "Sayint", "Tehuledere", "Tenta", "Wegde", "Were Babu", "Were Ilu"],
        "Wag Hemra": ["Aberegelle", "Dehana", "Gazbibla", "Sehala", "Soqota (town)", "Soqota Zuria", "Zikuala"],
        "West Gojjam": ["Bahir Dar Zuria", "Bure", "Debub Achefer", "Dega Damot", "Dembecha", "Finote Selam (town)", "Jabi Tehnan", "Kuarit", "Mecha", "Sekela", "Semien Achefer", "Wemberma", "Yilmana Densa"],
        "Bahir Dar (special zone)": ["Bahir Dar (town)"]
    },
    "Benishangul-Gumuz": {
        "Asosa": ["Asosa", "Bambasi", "Komesha", "Horazab", "Menge", "Oda Bildigilu", "Sherkole"],
        "Kamashi": ["Agalo Mite", "Belo Jegonfoy", "Kamashi", "Sadal", "Yaso"],
        "Metekel": ["Bulen", "Dangur", "Dibate", "Guba", "Mandura", "Wenbera", "Pawe"]
    },
    "Gambela": {
        "Anuak": ["Abobo", "Dimma", "Gambela (town)", "Gambela Zuria", "Gog", "Jor", "Akobo"],
        "Mezhenger": ["Godere", "Mengesh"],
        "Nuer": ["Jikawo", "Lare", "Akobo"]
    },
    "Harari": {
        "Harari": ["Amir-Nur Woreda", "Abadir Woreda", "Shenkor Woreda", "Jin'Eala Woreda", "Aboker Woreda", "Hakim Woreda", "Sofi Woreda", "Erer Woreda", "Dire-Teyara Woreda"]
    },
    "Oromia": {
        "Arsi": ["Amigna", "Aseko", "Asella", "Bale Gasegar", "Chole", "Digeluna Tijo", "Diksis", "Dodota", "Enkelo Wabe", "Gololcha", "Guna", "Hitosa", "Jeju", "Limuna Bilbilo", "Lude Hitosa", "Merti", "Munesa", "Robe", "Seru", "Sire", "Sherka", "Sude", "Tena", "Tiyo", "Ziway Dugda"],
        "Bale": ["Agarfa", "Berbere", "Dawe Kachen", "Dawe Serara", "Delo Menna", "Dinsho", "Gasera", "Ginir", "Goba (woreda)", "Goba (town)", "Gololcha", "Goro", "Guradamole", "Harena Buluk", "Legehida", "Meda Welabu", "Raytu", "Robe (town)", "Seweyna", "Sinana"],
        "Borena": ["Abaya", "Arero", "Bule Hora", "Dire", "Dugda Dawa", "Gelana", "Miyu", "Moyale", "Teltele", "Yabelo", "Dehas", "Dillo", "Malka Soda"],
        "East Hararghe": ["Babille", "Bedeno", "Chinaksen", "Deder", "Fedis", "Girawa", "Gola Oda", "Goro Gutu", "Gursum", "Haro Maya", "Jarso", "Kersa", "Kombolcha", "Kurfa Chele", "Malka Balo", "Meta", "Meyumuluke", "Midega Tola", "Deder town"],
        "East Shewa": ["Ada'a", "Adama Zuria", "Adami Tullu and Jido Kombolcha", "Bishoftu (town)", "Bora", "Dugda", "Boset", "Fentale", "Gimbichu", "Liben", "Lome", "Ziway (town)"],
        "East Welega": ["Bonaya Boshe", "Diga", "Gida Ayana", "Kiremu", "Gobu Seyo", "Gudeya Bila", "Guto Gida", "Haro Limmu", "Leka Dulecha", "Ibantu", "Jimma Arjo", "Limmu", "Nekemte (town)", "Nunu Kumba", "Sasiga", "Sibu Sire", "Wama Hagalo", "Wayu Tuka"],
        "Guji": ["Adola", "Ana Sora", "Bore", "Dima", "Girja", "Hambela Wamena", "Harenfema", "Kebri Mangest (town)", "Kercha", "Liben", "Negele Borana (town)", "Odo Shakiso", "Uraga", "Wadera"],
        "Horo Guduru Welega Zone": ["Abay Chomen", "Abe Dongoro", "Amuru", "Guduru", "Hababo Guduru", "Horo", "Jardega Jarte", "Jimma Genete", "Jimma Rare", "Shambu (town)"],
        "Illubabor Zone": ["Illubabor"],
        "Jimma": ["Agaro (town)", "Chora Botor", "Dedo", "Gera", "Gomma", "Guma", "Kersa", "Limmu Kosa", "Limmu Sakka", "Mana", "Omo Nada", "Seka Chekorsa", "Setema", "Shebe Senbo", "Sigmo", "Sokoru", "Tiro Afeta"],
        "Kelam Welega": ["Anfillo", "Dale Sedi", "Dale Wabera", "Dembidolo (town)", "Gawo Kebe", "Gidami", "Hawa Gelan", "Jimma Horo", "Lalo Kile", "Sayo", "Yemalogi Welele"],
        "North Shewa": ["Abichuna Gne'a", "Aleltu", "Debre Libanos", "Degem", "Dera", "Fiche (town)", "Gerar Jarso", "Hidabu Abote", "Jidojidda", "Kembibit", "Kuyu Garba Guracha", "Sendafa", "Wara Jarso", "Wuchalemuke", "Yaya Gulele"],
        "Southwest Shewa": ["Amaya", "Becho", "Dawo", "Elu", "Goro", "Kersana Malima", "Seden Sodo", "Sodo Dacha", "Tole", "Waliso (woreda)", "Waliso (town)", "Wonchi"],
        "West Arsi": ["Adaba", "Arsi Negele", "Dodola", "Gedeb Asasa", "Kofele", "Kokosa", "Kore", "Nensebo", "Seraro", "Shala", "Shashamene (town)", "Shashamene Zuria"],
        "West Haraghe": ["Anchar", "Badessa (town)", "Boke", "Chiro (town)", "Chiro Zuria", "Gemechis", "Darolebu", "Doba", "Guba Koricha", "Habro", "Kuni", "Mesela", "Mieso", "Tulo", "Hawi Gudina"],
        "West Shewa": ["Abuna Ginde Beret", "Ada'a Berga", "Ambo (town)", "Ginchi (town)", "Ambo (woreda)", "Bako Tibe", "Cheliya", "Dano", "Dendi", "Ejere", "Elfata", "Ginde Beret", "Jaldu", "Jibat", "Meta Robi", "Midakegn", "Nono", "Dire Enchini", "Toke Kutaye"],
        "West Welega": ["Ayra", "Babo Gambela", "Begi", "Boji Chokorsa", "Boji Dirmaji", "Genji", "Gimbi (woreda)", "Gimbi (town)", "Guliso", "Haru", "Homa", "Jarso", "Kondala", "Kiltu Kara", "Lalo Asabi", "Mana Sibu", "Nejo", "Nole Kaba", "Sayo Nole", "Yubdo"],
        "Adama (special zone)": ["Adama"],
        "Jimma (special zone)": ["Jimma"],
        "Oromia-Finfinne (special zone)": ["Akaki", "Bereh", "Burayu (town)", "Holeta Genet (town)", "Mulo", "Sebeta Hawas", "Sebeta (town)", "Sendafa (town)", "Walmara"]
    },
    "Somali": {
        "Afder": ["Hargelle", "Baarey", "Cherati", "Ceelgari", "Dolobay", "Iimey galbeed", "Raaso", "God God", "Qooxle"],
        "Jarar": ["Aware", "Dhadax-buur", "Dhagax-madow", "Gunagado", "Gashamo", "Birqod", "Dig", "Bilcil buur", "Daroor", "Araarso", "Yoocaale"],
        "Nogob": ["Ceelweyne", "Dhuxun", "Gerbo", "Xaraarey", "Ayun", "Hor-shagah", "Segeg"],
        "Gode(Shabelle)": ["Cadaadle", "Danan", "Ferfer", "Beer Caano", "Gode", "Iimey bari", "Kelafo", "Mustahil", "Elale", "Abaqorow"],
        "Fafan": ["Tuli Guled", "Awbere", "Babille", "Gursum", "Harshin", "Jijiga", "Kebri Beyah", "Goljano", "Qooraan", "Harawo", "Jigjiga Waqooyi", "Jigjiga Galbeed"],
        "Korahe": ["Dhooboweyn", "Kebri Dahar", "Sheygoosh", "Shilaabo", "Marsin", "Higloley", "Las Dharkaynle", "Kudunbuur", "Bodalay", "Ceel-Ogaden"],
        "Liben": ["Liben"],
        "Sitti": ["Afdem", "Ayesha", "Dembel", "Erer", "Mieso", "Shinile", "Hadhagaale", "Geblalu", "Gota biki"],
        "Dollo": ["Bokh", "Danot", "Geladi", "Werder", "Daratole", "Galxamur"]
    },
    "Southern Nations": {
        "Bench Maji": ["Bero", "Debub Bench", "Guraferda", "Maji", "Meinit Goldiya", "Meinit Shasha", "Mizan Aman (town)", "Semien Bench", "She Bench", "Sheko", "Surma"],
        "Dawro": ["Gena Bosa", "Isara", "Loma", "Mareka", "Tocha"],
        "Gamo Gofa": ["Arba Minch (town)", "Arba Minch Zuria", "Bonke", "Boreda", "Chencha", "Dita", "Deramalo", "Demba Gofa", "Geze Gofa", "Kemba", "Kucha", "Melokoza", "Mirab Abaya", "Oyda", "Sawla (town)", "Uba Debretsehay", "Zala"],
        "Gedeo": ["Bule", "Dila (town)", "Dila Zuria", "Gedeb", "Kochere", "Wenago", "Yirgachefe"],
        "Gurage": ["Abeshge", "Butajira (town)", "Cheha", "Endegagn", "Enemorina Eaner", "Ezha", "Geta", "Gumer", "Kebena", "Gedebano Gutazer Welene", "Mareko", "Meskane", "Muhor Na Aklil", "Soddo", "Welkite (town)"],
        "Hadiya": ["Ana Lemo", "Duna", "Gibe", "Gomibora", "Hosaena (town)", "Limo", "Mirab Badawacho", "Misha", "Misraq Badawacho", "Shashogo", "Soro", "Gimbichu"],
        "Keffa": ["Bita", "Bonga (town)", "Chena", "Cheta", "Decha", "Gesha", "Gewata", "Ginbo", "Menjiwo", "Sayilem", "Telo"],
        "Kembata Tembaro": ["Angacha", "Damboya", "Doyogena", "Durame (town)", "Hadero Tunto", "Kacha Bira", "Kedida Gamela", "Tembaro"],
        "Sheka": ["Anderacha", "Masha", "Yeki"],
        "Sidama": ["Aleta Wendo", "Arbegona", "Aroresa", "Awasa Zuria", "Bensa", "Bona Zuria", "Boricha", "Bursa", "Chere", "Chuko", "Dale", "Dara", "Gorche", "Hula", "Loko Abaya", "Malga", "Shebedino", "Wensho", "Wondo Genet"],
        "Silt'e": ["Alicho Werero", "Dalocha", "Lanfro", "Mirab Azernet Berbere", "Misraq Azernet Berbere", "Sankurra", "Silte", "Wulbareg"],
        "South Omo": ["Bako Gazer", "Bena Tsemay", "Gelila", "Hamer", "Kuraz", "Male", "Nyangatom", "Selamago"],
        "Wolayita": ["Boloso Bombe", "Boloso Sore", "Damot Gale", "Damot Pulasa", "Damot Sore", "Damot Weyde", "Diguna Fango", "Humbo", "Kindo Didaye", "Kindo Koysha", "Offa", "Sodo (town)", "Sodo Zuria"],
        "Alaba (special woreda)": ["Alaba"],
        "Amaro (special woreda)": ["Amaro"],
        "Basketo (special woreda)": ["Basketo"],
        "Burji (special woreda)": ["Burji"],
        "Dirashe (special woreda)": ["Dirashe"],
        "Konso (special woreda)": ["Konso"],
        "Konta (special woreda)": ["Konta"],
        "Yem (special woreda)": ["Yem"]
    },
    "Tigray": {
        "Central Tigray": ["Abergele", "Adwa", "Degua Tembien", "Enticho", "Kola Tembien", "La'ilay Maychew", "Mereb Lehe", "Naeder Adet", "Tahtay Maychew", "Werie Lehe"],
        "East Tigray": ["Atsbi Wenberta", "Ganta Afeshum", "Gulomahda", "Hawzen", "Irob", "Saesi Tsaedaemba", "Wukro"],
        "North West Tigray": ["Asgede Tsimbela", "La'ilay Adiyabo", "Medebay Zana", "Tahtay Adiyabo", "Tahtay Koraro", "Tselemti"],
        "South Tigray": ["Alaje", "Alamata", "Endamehoni", "Ofla", "Raya Azebo"],
        "South East Tigray": ["Enderta", "Hintalo Wajirat", "Samre"],
        "West Tigray": ["Kafta Humera", "Tsegede", "Wolqayt"],
        "Mekele (special zone)": ["Mek'ele"]
    },
    "Addis Ababa": {
        "Addis Ababa": ["Addis Ketema", "Akaky Kaliti", "Arada", "Bole", "Gullele", "Kirkos", "Kolfe Keranio", "Lideta", "Nifas Silk-Lafto", "Yeka"]
    },
    "Dire Dawa": {
        "Dire Dawa": ["Dire Dawa -City", "Dire Dawa -Non-urban"]
    },
    "Other": {
        "Other": ["Other"]
    }
};

function loadZones() {
    const reg = document.getElementById('region').value;
    const z = document.getElementById('zone');
    const w = document.getElementById('woreda');
    const k = document.getElementById('kebele');
    
    // Reset dependent dropdowns
    z.innerHTML = '<option value="">Select Zone</option>';
    w.innerHTML = '<option value="">Select Woreda</option>';
    k.innerHTML = '<option value="">Select Kebele</option>';
    
    if(locationData[reg]) {
        Object.keys(locationData[reg]).forEach(key => {
            z.add(new Option(key, key));
        });
    } else if (reg === 'Other') {
        z.add(new Option('Other', 'Other'));
    }
}

function loadWoredas() {
    const reg = document.getElementById('region').value;
    const zn = document.getElementById('zone').value;
    const w = document.getElementById('woreda');
    const k = document.getElementById('kebele');
    
    // Reset dependent dropdowns
    w.innerHTML = '<option value="">Select Woreda</option>';
    k.innerHTML = '<option value="">Select Kebele</option>';
    
    if(locationData[reg] && locationData[reg][zn]) {
        locationData[reg][zn].forEach(wd => {
            w.add(new Option(wd, wd));
        });
    } else {
        // Fallback for custom entries
        w.add(new Option('Other', 'Other'));
    }
}

function loadKebeles() {
    const k = document.getElementById('kebele');
    k.innerHTML = '<option value="">Select Kebele</option>';
    // Generate Kebele 1 - 50 for more options
    for(let i=1; i<=50; i++) {
        const val = 'Kebele ' + i;
        k.add(new Option(val, val));
    }
}
</script>

</body>



</body>




    <div class="form-group">
        <label for="educationLevel">Education Level *</label>
        <select id="educationLevel" name="educationLevel" required>
            <option value="">Select Education Level</option>
            <option value="none">No Formal Education</option>
            <option value="primary">Primary School</option>
            <option value="secondary">Secondary School</option>
            <option value="diploma">Diploma</option>
            <option value="bachelor">Bachelor's Degree</option>
            <option value="master">Master's Degree</option>
            <option value="phd">PhD</option>
            <option value="other">Other</option>
        </select>
    </div>




    <!--<div class="form-group">
        <label for="previousSchool">Previous School Attended *</label>
        <select id="previousSchool" required>
            <option value="">Select School</option>
            <option value="primary_school">Primary School</option>
            <option value="secondary_school">Secondary School</option>
            <option value="high_school">High School</option>
            <option value="vocational_school">Vocational/Technical School</option>
            <option value="college">College</option>
            <option value="university">University</option>
            <option value="other">Other</option>
        </select>
    </div>
-->



    <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
        <label for="primarySchool" style="width: 150px;">Primary School *</label>
        <input type ="text " rows="2" placeholder="Enter details about Primary School" style="flex: 1;" required></textarea>
    </div>

    <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
        <label for="Secondary School" style="width: 150px;">Secondary School *</label>
        <input type ="text " rows="2" placeholder="Enter details about Secondary School" style="flex: 1;" required></textarea>
    </div>

    <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
        <label for="College" style="width: 150px;">College  *</label>
        <input type ="text " rows="2" placeholder="Enter details about College" style="flex: 1;" required></textarea>
    </div>

    <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
        <label for="university" style="width: 150px;">university *</label>
        <input type ="text " rows="2" placeholder="Enter details about university" style="flex: 1;" required></textarea>
    </div>




    <div class="form-group">
        <label for="allDepartments">Department *</label>
        <select id="allDepartments" name="department" required onchange="checkOtherDepartment()">
            <option value="">Select Department</option>
            <option value="general_medicine">General Medicine</option>
            <option value="pediatrics">Pediatrics</option>
            <option value="obstetrics_gynecology">Obstetrics & Gynecology</option>
            <option value="surgery">Surgery</option>
            <option value="orthopedics">Orthopedics</option>
            <option value="dermatology">Dermatology</option>
            <option value="ophthalmology">Ophthalmology</option>
            <option value="dentistry">Dentistry</option>
            <option value="psychiatry">Psychiatry / Mental Health</option>
            <option value="rehabilitation">Rehabilitation / Physiotherapy</option>
            <option value="nutrition">Nutrition / Dietetics</option>
            <option value="laboratory">Laboratory</option>
            <option value="radiology">Radiology / Imaging</option>
            <option value="pathology">Pathology</option>
            <option value="emergency">Emergency / ER</option>
            <option value="intensive_care">Intensive Care Unit (ICU)</option>
            <option value="cardiology">Cardiology</option>
            <option value="neurology">Neurology</option>
            <option value="oncology">Oncology</option>
            <option value="pharmacy">Pharmacy</option>
            <option value="medical_records">Medical Records</option>
            <option value="hospital_administration">Administration</option>
            <option value="billing_finance">Billing & Finance</option>
            <option value="human_resources">Human Resources</option>
            <option value="housekeeping">Housekeeping</option>
            <option value="security">Security</option>
            <option value="transportation">Transportation / Ambulance</option>
            <option value="maintenance">Maintenance / Engineering</option>
            <option value="information_technology">Information Technology (IT)</option>
            <option value="other">Other</option>
        </select>
    </div>


<!-- Textarea for Other Department -->
<div class="form-group" id="otherDepartmentDiv" style="display:none;">
    <label for="otherDepartment">Specify Other Department *</label>
    <textarea id="otherDepartment" rows="2" placeholder="Enter department name"></textarea>
</div>

<script>
function checkOtherDepartment() {
    const select = document.getElementById("allDepartments");
    const otherDiv = document.getElementById("otherDepartmentDiv");
    
    if(select.value === "other") {
        otherDiv.style.display = "block";
    } else {
        otherDiv.style.display = "none";
    }
}
</script>




    <div class="form-group">
        <label for="bankName">Bank *</label>
        <select id="bankName" name="bankName" required onchange="checkBankSelection()">
            <option value="">Select Bank</option>
            <option value="commercial_bank">Commercial Bank of Ethiopia</option>
            <option value="dashen_bank">Dashen Bank</option>
            <option value="abreha_weatsbeha_bank">Abreha Weatsbeha Bank</option>
            <option value="zemen_bank">Zemen Bank</option>
            <option value="nib_international_bank">Nib International Bank</option>
        </select>
    </div>

<!-- Textarea for Bank Account Number -->
<div class="form-group" id="bankAccountDiv" style="display:none;">
    <label for="bankAccount">Bank Account Number *</label>
    <textarea id="bankAccount" name="bankAccount" rows="2" placeholder="Enter bank account number" required></textarea>
</div>

<script>
function checkBankSelection() {
    const bankSelect = document.getElementById("bankName");
    const accountDiv = document.getElementById("bankAccountDiv");

    if(bankSelect.value === "commercial_bank") {
        accountDiv.style.display = "block";
    } else {
        accountDiv.style.display = "none";
    }
}
</script>




    <div class="form-group">
        <label for="jobLevel">Job Level *</label>
        <select id="jobLevel" name="jobLevel" required onchange="checkOtherJobLevel()">
            <option value="">Select Job Level</option>
            <option value="entry">Entry Level</option>
            <option value="junior">Junior</option>
            <option value="mid">Mid Level</option>
            <option value="senior">Senior</option>
            <option value="lead">Lead / Team Lead</option>
            <option value="manager">Manager</option>
            <option value="director">Director</option>
            <option value="executive">Executive / C-Level</option>
            <option value="other">Other</option>
        </select>
    </div>


<!-- Textarea for Other Job Level -->
<div class="form-group" id="otherJobLevelDiv" style="display:none;">
    <label for="otherJobLevel">Specify Other Job Level *</label>
    <textarea id="otherJobLevel" name="otherJobLevel" rows="2" placeholder="Enter job level"></textarea>
</div>

<script>
function checkOtherJobLevel() {
    const select = document.getElementById("jobLevel");
    const otherDiv = document.getElementById("otherJobLevelDiv");
    
    if(select.value === "other") {
        otherDiv.style.display = "block";
    } else {
        otherDiv.style.display = "none";
    }
}
</script>




    <div class="form-group">
        <label for="maritalStatus">Marital Status *</label>
        <select id="maritalStatus" name="maritalStatus" required onchange="checkMaritalStatus()">
            <option value="">Select Marital Status</option>
            <option value="single">Single</option>
            <option value="married">Married</option>
            <option value="divorced">Divorced</option>
            <option value="widowed">Widowed</option>
            <option value="separated">Separated</option>
            <option value="other">Other</option>
        </select>
    </div>




<body>

<div class="form-group">
    <label for="warranty_status">Warranty Status *</label>
    <select id="warranty_status" name="warranty_status" required onchange="toggleWarrantyFields()">
        <option value="">Select Status</option>
        <option value="no">No</option>
        <option value="yes">Yes</option>
    </select>
</div>

<!-- Hidden fields that show only if warranty = yes -->
<div id="warranty_fields" style="display:none;">

    <div class="form-group">
        <label for="person_name">Person Name *</label>
        <input type="text" id="person_name" name="person_name" placeholder="Enter Name">
    </div>

    <div class="form-group">
        <label for="warranty_woreda">Woreda *</label>
        <input type="text" id="warranty_woreda" name="warranty_woreda" placeholder="Enter Woreda">
    </div>

    <div class="form-group">
        <label for="warranty_kebele">Kebele *</label>
        <select id="warranty_kebele" name="warranty_kebele">
            <option value="">Select Kebele</option>
        </select>
    </div>

    <div class="form-group">
        <label for="warranty_phone">Phone Number *</label>
        <input type="text" id="warranty_phone" name="warranty_phone" placeholder="Enter Phone Number">
    </div>

    <div class="form-group">
        <label for="warranty_type">Warranty Type *</label>
        <select id="warranty_type" name="warranty_type">
            <option value="">Select Type</option>
            <option value="loan">Loan</option>
            <option value="employee">For Employee</option>
        </select>
    </div>

    <div class="form-group">
        <label for="scan_file">Scan File *</label>
        <input type="file" id="scan_file">
    </div>

</div>

<script>
function toggleWarrantyFields() {
    const status = document.getElementById("warranty_status").value;
    const fields = document.getElementById("warranty_fields");
    const kebeleSelect = document.getElementById("kebele");

    if (status === "yes") {
        fields.style.display = "block";

        // Populate kebele numbers 01-50
        kebeleSelect.innerHTML = '<option value="">Select Kebele</option>';
        for (let i = 1; i <= 50; i++) {
            const num = i.toString().padStart(2, '0');
            const option = document.createElement("option");
            option.value = `Kebele ${num}`;
            option.textContent = `Kebele ${num}`;
            kebeleSelect.appendChild(option);
        }

    } else {
        fields.style.display = "none";
    }
}
</script>

</body>




<body>

<div class="form-group">
    <label for="criminal_status">Criminal Status *</label>
    <select id="criminal_status" name="criminal_status" required onchange="toggleCriminalFields()">
        <option value="">Select Status</option>
        <option value="no">No</option>
        <option value="yes">Yes</option>
    </select>
</div>

<div id="criminal_fields" style="display:none;">

    <div class="form-group">
        <label for="criminal_file">Criminal Record Scan Document *</label>
        <input type="file" id="criminal_file" accept=".pdf,.jpg,.jpeg,.png">
    </div>

</div>

<script>
function toggleCriminalFields() {
    const status = document.getElementById("criminal_status").value;
    const fields = document.getElementById("criminal_fields");

    if (status === "yes") {
        fields.style.display = "block";
    } else {
        fields.style.display = "none";
        document.getElementById("criminal_file").value = "";
    }
}
</script>

</body>




<div class="form-group">
    <label for="fin_id">Fayda FIN / FIN ID *</label>
    <input type="text" id="fin_id" name="fin_id" placeholder="Enter FIN or FIN ID" required>
</div>

<div class="form-group">
    <label for="fin_scan">Scan FIN Document *</label>
    <input type="file" id="fin_scan" accept="image/*,.pdf">
</div>




<body>

<div class="form-group">
    <label for="loan_status">Loan Case *</label>
    <select id="loan_status" name="loan_status" required onchange="toggleLoanFields()">
        <option value="">Select Status</option>
        <option value="no">No</option>
        <option value="yes">Yes</option>
    </select>
</div>

<div id="loan_fields" style="display:none;">

    <div class="form-group">
        <label for="loan_file">Loan Document Scan *</label>
        <input type="file" id="loan_file" accept=".pdf,.jpg,.jpeg,.png">
    </div>

</div>

<script>
function toggleLoanFields() {
    const status = document.getElementById("loan_status").value;
    const fields = document.getElementById("loan_fields");
    const fileInput = document.getElementById("loan_file");

    if (status === "yes") {
        fields.style.display = "block";
        fileInput.required = true;
    } else {
        fields.style.display = "none";
        fileInput.required = false;
        fileInput.value = "";
    }
}
</script>

</body>




<!-- Textarea for Other Marital Status -->
<div class="form-group" id="otherMaritalStatusDiv" style="display:none;">
    <label for="otherMaritalStatus">Specify Marital Status *</label>
    <textarea id="otherMaritalStatus" name="otherMaritalStatus" rows="2" placeholder="Enter marital status"></textarea>
</div>

<script>
function checkMaritalStatus() {
    const select = document.getElementById("maritalStatus");
    const otherDiv = document.getElementById("otherMaritalStatusDiv");

    if(select.value === "other") {
        otherDiv.style.display = "block";
    } else {
        otherDiv.style.display = "none";
    }
}
</script>




<!DOCTYPE html>
<html>
<head>
    <title>Scan Document</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
        }

        .scan-btn {
            background-color: #28a745;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }

        .scan-btn:hover {
            background-color: #218838;
        }

        .preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 15px;
        }

        .preview-box {
            width: 160px;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 6px;
            text-align: center;
            background-color: #f9f9f9;
        }

        .preview-box img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 4px;
        }

        .preview-box p {
            font-size: 12px;
            margin-top: 6px;
            word-break: break-word;
        }
    </style>
</head>
<body>

<div class="form-group">
    <label>Employee Document Scan (Primary to Current)</label>

    <!-- Hidden input: allows file OR camera -->
    <input 
        type="file" 
        id="documents" 
        accept="image/*,.pdf" 
        capture="environment"
        style="display:none;" 
        onchange="addDocument()"
    >

    <!-- Scan Button -->
    <button type="button" class="scan-btn"
        onclick="document.getElementById('documents').click();">
        Scan Document
    </button>
</div>

<!-- Preview Area -->
<div class="preview-container" id="previewContainer"></div>

<script>
function addDocument() {
    const input = document.getElementById("documents");
    const file = input.files[0];
    if (!file) return;

    const previewContainer = document.getElementById("previewContainer");
    const previewBox = document.createElement("div");
    previewBox.className = "preview-box";

    if (file.type.startsWith("image/")) {
        const img = document.createElement("img");
        img.src = URL.createObjectURL(file);
        previewBox.appendChild(img);
    } else {
        const pdfIcon = document.createElement("img");
        pdfIcon.src = "https://upload.wikimedia.org/wikipedia/commons/8/87/PDF_file_icon.svg";
        previewBox.appendChild(pdfIcon);
    }

    const fileName = document.createElement("p");
    fileName.textContent = file.name;

    previewBox.appendChild(fileName);
    previewContainer.appendChild(previewBox);

    input.value = "";
}
</script>

</body>
</html>




<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Language Selector</title>
<style>
body { font-family: Arial, sans-serif; padding: 40px; }
.language-selector { margin-bottom: 20px; }
.language-selector select, .language-selector input {
    padding: 8px 12px;
    font-size: 16px;
    border-radius: 5px;
    border: 1px solid #ccc;
    margin-top: 5px;
}
#otherLanguageDiv { display: none; margin-top: 10px; }
</style>
</head>
<body>

<div class="language-selector">
    <label for="language">Select Language *</label>
    <select id="language" onchange="checkOtherLanguage()">
        <option value="">--Select Language--</option>
        <option value="english">English</option>
        <option value="amharic">Amharic</option>
        <option value="oromifa">Oromifa</option>
        <option value="tigrigna">Tigrigna</option>
        <option value="afar">Afar</option>
        <option value="other">Other</option>
    </select>

    <div id="otherLanguageDiv">
        <label for="otherLanguage">Specify Other Language *</label>
        <input type="text" id="otherLanguage" name="otherLanguage" placeholder="Enter language">
    </div>
</div>

<script>
function checkOtherLanguage() {
    const langSelect = document.getElementById("language");
    const otherDiv = document.getElementById("otherLanguageDiv");

    if(langSelect.value === "other") {
        otherDiv.style.display = "block";
    } else {
        otherDiv.style.display = "none";
    }
}
</script>

</body>
</html>




<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Employee Leave Request</title>
<style>
body { font-family: Arial, sans-serif; padding: 30px; }
.form-group { margin-bottom: 20px; }
label { display: block; font-weight: bold; margin-bottom: 8px; }
select, textarea, input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; }
.scan-btn { background-color: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; }
.scan-btn:hover { background-color: #218838; }
.preview-container { display: flex; flex-wrap: wrap; gap: 15px; margin-top: 15px; }
.preview-box { width: 160px; border: 1px solid #ccc; padding: 10px; border-radius: 6px; text-align: center; background-color: #f9f9f9; }
.preview-box img { width: 100%; height: 150px; object-fit: cover; border-radius: 4px; }
.preview-box p { font-size: 12px; margin-top: 6px; word-break: break-word; }
</style>
</head>
<body>

   <!-- <h2>Employee Leave Request</h2>-->

<div class="form-group">
    <label for="leaveRequest">Request Leave? *</label>
    <select id="leaveRequest" name="leaveRequest" onchange="checkLeaveRequest()">
        <option value="">--Select--</option>
        <option value="yes">Yes</option>
        <option value="no">No</option>
    </select>
</div>

<!-- Leave Document Scan Section -->
<div class="form-group" id="leaveDocumentDiv" style="display:none;">
    <label>Upload Leave Document</label>
    <input type="file" id="leaveDocuments" accept="image/*,.pdf" capture="environment" style="display:none;" onchange="addLeaveDocument()">
    <button type="button" class="scan-btn" onclick="document.getElementById('leaveDocuments').click();">Scan Document</button>

    <div class="preview-container" id="leavePreviewContainer"></div>
</div>

<script>
// Show/hide leave document section
function checkLeaveRequest() {
    const leaveSelect = document.getElementById("leaveRequest");
    const docDiv = document.getElementById("leaveDocumentDiv");

    if(leaveSelect.value === "yes") {
        docDiv.style.display = "block";
    } else {
        docDiv.style.display = "none";
        document.getElementById("leavePreviewContainer").innerHTML = ""; // clear previous previews
    }
}

// Add scanned document preview
function addLeaveDocument() {
    const fileInput = document.getElementById("leaveDocuments");
    const previewContainer = document.getElementById("leavePreviewContainer");

    if(fileInput.files && fileInput.files[0]) {
        const file = fileInput.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewBox = document.createElement("div");
            previewBox.classList.add("preview-box");

            if(file.type.startsWith("image/")) {
                previewBox.innerHTML = `<img src="${e.target.result}" alt="Document"><p>${file.name}</p>`;
            } else {
                previewBox.innerHTML = `<p>${file.name}</p>`;
            }

            previewContainer.appendChild(previewBox);
        }
        reader.readAsDataURL(file);
    }
}
</script>

</body>
</html>




                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="department">Department *</label>
                        <select id="department" name="department_assigned" required>
                            <option value="">Select Department</option>
                            <option value="medical">Medical</option>
                            <option value="administration">Administration</option>
                            <option value="technical">Technical</option>
                            <option value="support">Support</option>
                            <option value="finance">Finance</option>
                            <option value="hr">Human Resources</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="position">Position *</label>
                        <input type="text" id="position" name="position" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="joinDate">Join Date *</label>
                        <input type="date" id="joinDate" name="joinDate" required>
                    </div>
                    <div class="form-group">
                        <label for="salary">Salary</label>
                        <input type="number" id="salary" name="salary" step="0.01">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="employmentType">Employment Type</label>
                        <select id="employmentType" name="employmentType">
                            <option value="full-time">Full-time</option>
                            <option value="part-time">Part-time</option>
                            <option value="contract">Contract</option>
                            <option value="intern">Intern</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="active">Active</option>
                            <option value="on-leave">On Leave</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address"></textarea>
                </div>

                <div class="form-group">
                    <label for="emergencyContact">Emergency Contact</label>
                    <input type="text" id="emergencyContact" name="emergencyContact">
                </div>

                <div class="form-group">
                    <label for="employeeDocuments">Upload Documents (CV, Certificates, etc.)</label>
                    <input type="file" id="employeeDocuments" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    <small style="color: var(--gray); font-size: 0.85rem;">You can select multiple files. Supported formats: PDF, DOC, DOCX, JPG, PNG</small>
                    <div id="fileList" style="margin-top: 10px;"></div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="submit-btn">Add Employee</button>
                    <button type="button" class="cancel-btn" id="cancelEmployeeBtn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="scripts.js"></script>
    <script>
        // Employee page specific JavaScript
        let allEmployees = [];
        const employeeSearch = document.getElementById('employeeSearch');
        const departmentFilter = document.getElementById('departmentFilter');
        const statusFilter = document.getElementById('statusFilter');

        // Load employees on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadEmployees();
        });

        // Load employees from database
        function loadEmployees() {
            fetch('get_employees.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        allEmployees = data.employees;
                        displayEmployees(allEmployees);
                    } else {
                        console.error('Failed to load employees:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading employees:', error);
                });
        }

        // Display employees in table
        function displayEmployees(employees) {
            const tbody = document.getElementById('employeeTableBody');
            tbody.innerHTML = '';

            if (employees.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px; color: var(--gray);">No employees found</td></tr>';
                return;
            }

            employees.forEach(employee => {
                const fullName = `${employee.first_name} ${employee.middle_name ? employee.middle_name + ' ' : ''}${employee.last_name}`;
                const initials = fullName.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);

                const joinDate = new Date(employee.join_date).toLocaleDateString('en-GB');

                const departmentClass = getDepartmentClass(employee.department_assigned);
                const departmentName = getDepartmentName(employee.department_assigned);

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <div class="employee-info">
                            <div class="employee-avatar">${initials}</div>
                            <div>
                                <div class="employee-name">${fullName}</div>
                                <div class="employee-id">${employee.email}</div>
                            </div>
                        </div>
                    </td>
                    <td>${employee.employee_id}</td>
                    <td><span class="department-badge ${departmentClass}">${departmentName}</span></td>
                    <td>${employee.position || 'Not specified'}</td>
                    <td>${joinDate}</td>
                    <td><span class="status-badge ${employee.status}">${employee.status.replace('-', ' ')}</span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn view" onclick="viewEmployee(${employee.id})"><i class="fas fa-eye"></i></button>
                            <button class="action-btn edit" onclick="editEmployee(${employee.id})"><i class="fas fa-edit"></i></button>
                            <button class="action-btn delete" onclick="deleteEmployee(${employee.id}, '${fullName}')"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Get department class for styling
        function getDepartmentClass(dept) {
            const classes = {
                'medical': 'medical',
                'administration': 'admin',
                'technical': 'technical',
                'support': 'support',
                'finance': 'admin',
                'hr': 'admin'
            };
            return classes[dept] || 'support';
        }

        // Get department display name
        function getDepartmentName(dept) {
            const names = {
                'medical': 'Medical',
                'administration': 'Administration',
                'technical': 'Technical',
                'support': 'Support',
                'finance': 'Finance',
                'hr': 'Human Resources'
            };
            return names[dept] || dept;
        }

        // Live search and filter functionality
        function filterEmployees() {
            const searchTerm = employeeSearch.value.toLowerCase();
            const departmentValue = departmentFilter.value;
            const statusValue = statusFilter.value;

            const filtered = allEmployees.filter(employee => {
                const fullName = `${employee.first_name} ${employee.middle_name ? employee.middle_name + ' ' : ''}${employee.last_name}`.toLowerCase();
                const employeeId = employee.employee_id.toLowerCase();
                const position = (employee.position || '').toLowerCase();

                const matchesSearch = fullName.includes(searchTerm) ||
                                    employeeId.includes(searchTerm) ||
                                    position.includes(searchTerm);

                const matchesDepartment = departmentValue === '' || employee.department_assigned === departmentValue;
                const matchesStatus = statusValue === '' || employee.status === statusValue;

                return matchesSearch && matchesDepartment && matchesStatus;
            });

            displayEmployees(filtered);
        }

        employeeSearch.addEventListener('input', filterEmployees);
        departmentFilter.addEventListener('change', filterEmployees);
        statusFilter.addEventListener('change', filterEmployees);

        // Modal Functions
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        function closeAllModals() {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                modal.style.display = 'none';
            });
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                closeAllModals();
            }
        });

        // Close modal buttons
        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                closeAllModals();
            });
        });

        // Cancel buttons
        document.querySelectorAll('.cancel-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                resetEmployeeForm();
                closeAllModals();
            });
        });

        // Close modal with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeAllModals();
            }
        });

        // Table row actions
        document.querySelectorAll('.action-btn.view').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');
                const employeeName = row.querySelector('.employee-name').textContent;
                alert(`Viewing profile for ${employeeName}`);
            });
        });

        document.querySelectorAll('.action-btn.edit').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');
                const employeeName = row.querySelector('.employee-name').textContent;
                alert(`Editing profile for ${employeeName}`);
            });
        });

        document.querySelectorAll('.action-btn.delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');
                const employeeName = row.querySelector('.employee-name').textContent;
                if (confirm(`Are you sure you want to delete ${employeeName} from the system?`)) {
                    row.style.opacity = '0.5';
                    setTimeout(() => {
                        row.remove();
                        alert(`${employeeName} has been removed from the system.`);
                    }, 300);
                }
            });
        });

        // File Upload Handling
        const employeeDocuments = document.getElementById('employeeDocuments');
        const fileList = document.getElementById('fileList');

        employeeDocuments.addEventListener('change', function(e) {
            const files = e.target.files;
            fileList.innerHTML = '';

            if (files.length > 0) {
                const fileListDiv = document.createElement('div');
                fileListDiv.style.cssText = 'border: 1px solid var(--light-gray); border-radius: 5px; padding: 10px; background-color: var(--light); margin-top: 10px;';

                const fileListTitle = document.createElement('div');
                fileListTitle.textContent = `Selected Files (${files.length}):`;
                fileListTitle.style.cssText = 'font-weight: 600; margin-bottom: 8px; color: var(--primary);';
                fileListDiv.appendChild(fileListTitle);

                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const fileItem = document.createElement('div');
                    fileItem.style.cssText = 'display: flex; align-items: center; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #eee;';

                    const fileInfo = document.createElement('div');
                    fileInfo.style.cssText = 'display: flex; align-items: center; gap: 10px;';

                    const fileIcon = document.createElement('i');
                    fileIcon.className = getFileIcon(file.type);
                    fileIcon.style.cssText = 'color: var(--primary); width: 20px;';

                    const fileName = document.createElement('span');
                    fileName.textContent = file.name;
                    fileName.style.cssText = 'font-size: 0.9rem;';

                    const fileSize = document.createElement('span');
                    fileSize.textContent = formatFileSize(file.size);
                    fileSize.style.cssText = 'font-size: 0.8rem; color: var(--gray);';

                    fileInfo.appendChild(fileIcon);
                    fileInfo.appendChild(fileName);
                    fileInfo.appendChild(fileSize);

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                    removeBtn.style.cssText = 'background: none; border: none; color: var(--danger); cursor: pointer; padding: 2px 5px; border-radius: 3px;';
                    removeBtn.title = 'Remove file';
                    removeBtn.onclick = function() {
                        fileItem.remove();
                        updateFileCount();
                    };

                    fileItem.appendChild(fileInfo);
                    fileItem.appendChild(removeBtn);
                    fileListDiv.appendChild(fileItem);
                }

                fileList.appendChild(fileListDiv);
            }
        });

        function getFileIcon(mimeType) {
            if (mimeType.includes('pdf')) return 'fas fa-file-pdf';
            if (mimeType.includes('doc')) return 'fas fa-file-word';
            if (mimeType.includes('image')) return 'fas fa-file-image';
            return 'fas fa-file';
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function updateFileCount() {
            const files = employeeDocuments.files;
            if (files.length === 0) {
                fileList.innerHTML = '';
            }
        }

        // Form Submissions
        document.getElementById('employeeForm').addEventListener('submit', (e) => {
            e.preventDefault();

            const formData = new FormData(e.target);
            const isEditMode = e.target.getAttribute('data-mode') === 'edit';
            const employeeId = e.target.getAttribute('data-employee-id');

            // Add employee ID for edit mode
            if (isEditMode && employeeId) {
                formData.append('employee_id', employeeId);
            }

            // Add file uploads
            const fileInputs = ['scan_file', 'criminal_file', 'fin_scan', 'loan_file', 'leaveDocuments'];
            fileInputs.forEach(inputId => {
                const input = document.getElementById(inputId);
                if (input && input.files[0]) {
                    formData.append(inputId, input.files[0]);
                }
            });

            // Add multiple documents
            const employeeDocuments = document.getElementById('employeeDocuments');
            if (employeeDocuments && employeeDocuments.files) {
                for (let i = 0; i < employeeDocuments.files.length; i++) {
                    formData.append('employeeDocuments[]', employeeDocuments.files[i]);
                }
            }

            // Determine endpoint and method
            const endpoint = isEditMode ? 'edit_employee.php' : 'add_employee.php';

            // Submit to server
            fetch(endpoint, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const successMessage = isEditMode ?
                        'Employee updated successfully!' :
                        `Employee added successfully! Employee ID: ${data.employee_id || 'N/A'}`;
                    alert(successMessage);
                    closeAllModals();
                    resetEmployeeForm();
                    loadEmployees(); // Refresh the table
                } else {
                    alert(`Failed to ${isEditMode ? 'update' : 'add'} employee: ` + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(`An error occurred while ${isEditMode ? 'updating' : 'adding'} the employee.`);
            });
        });

        function resetEmployeeForm() {
            const form = document.getElementById('employeeForm');
            form.reset();
            form.removeAttribute('data-employee-id');
            form.removeAttribute('data-mode');
            document.getElementById('fileList').innerHTML = '';
            document.getElementById('addEmployeeModal').querySelector('.modal-title').textContent = 'Add New Employee';
            document.querySelector('.submit-btn').textContent = 'Add Employee';
        }

        // Employee action functions
        function viewEmployee(id) {
            // Find employee data
            const employee = allEmployees.find(emp => emp.id == id);
            if (employee) {
                const fullName = `${employee.first_name} ${employee.middle_name ? employee.middle_name + ' ' : ''}${employee.last_name}`;
                let details = `Employee Details:\n\n`;
                details += `Name: ${fullName}\n`;
                details += `Employee ID: ${employee.employee_id}\n`;
                details += `Email: ${employee.email}\n`;
                details += `Phone: ${employee.phone_number}\n`;
                details += `Department: ${getDepartmentName(employee.department_assigned)}\n`;
                details += `Position: ${employee.position}\n`;
                details += `Join Date: ${new Date(employee.join_date).toLocaleDateString()}\n`;
                details += `Status: ${employee.status}\n`;
                alert(details);
            }
        }

        function editEmployee(id) {
            // Find employee data
            const employee = allEmployees.find(emp => emp.id == id);
            if (!employee) {
                alert('Employee not found');
                return;
            }

            // Populate form with employee data
            populateEditForm(employee);

            // Change modal title and button
            document.getElementById('addEmployeeModal').querySelector('.modal-title').textContent = 'Edit Employee';
            document.querySelector('.submit-btn').textContent = 'Update Employee';

            // Open modal
            openModal('addEmployeeModal');
        }

        function populateEditForm(employee) {
            // Helper function to safely set values
            function setValue(id, value) {
                const element = document.getElementById(id);
                if (element) {
                    element.value = value || '';
                }
            }

            // Basic information
            setValue('firstName', employee.first_name);
            setValue('middleName', employee.middle_name);
            setValue('lastName', employee.last_name);
            setValue('email', employee.email);
            setValue('phone', employee.phone_number);

            // Personal details
            setValue('newPatientGender', employee.gender);
            setValue('dateOfBirth', employee.date_of_birth);
            setValue('religion', employee.religion);
            setValue('citizenship', employee.citizenship);
            setValue('otherCitizenship', employee.other_citizenship);

            // Location
            setValue('region', employee.region);
            setValue('zone', employee.zone);
            setValue('woreda', employee.woreda);
            setValue('kebele', employee.kebele);

            // Education
            setValue('educationLevel', employee.education_level);
            setValue('primarySchool', employee.primary_school);
            setValue('secondarySchool', employee.secondary_school);
            setValue('college', employee.college);
            setValue('university', employee.university);

            // Department and job
            setValue('allDepartments', employee.department);
            setValue('otherDepartment', employee.other_department);
            setValue('bankName', employee.bank_name);
            setValue('bankAccount', employee.bank_account);
            setValue('jobLevel', employee.job_level);
            setValue('otherJobLevel', employee.other_job_level);

            // Status
            setValue('maritalStatus', employee.marital_status);
            setValue('otherMaritalStatus', employee.other_marital_status);
            setValue('warranty_status', employee.warranty_status);
            setValue('person_name', employee.person_name);
            setValue('warranty_woreda', employee.warranty_woreda);
            setValue('warranty_kebele', employee.warranty_kebele);
            setValue('warranty_phone', employee.phone);
            setValue('warranty_type', employee.warranty_type);
            setValue('criminal_status', employee.criminal_status);
            setValue('fin_id', employee.fin_id);
            setValue('loan_status', employee.loan_status);
            setValue('language', employee.language);
            setValue('otherLanguage', employee.other_language);
            setValue('leaveRequest', employee.leave_request);

            // Employment details
            setValue('department', employee.department_assigned);
            setValue('position', employee.position);
            setValue('joinDate', employee.join_date ? employee.join_date.split(' ')[0] : '');
            setValue('salary', employee.salary);
            setValue('employmentType', employee.employment_type);
            setValue('status', employee.status);
            setValue('address', employee.address);
            setValue('emergencyContact', employee.emergency_contact);

            // Store employee ID for update
            document.getElementById('employeeForm').setAttribute('data-employee-id', employee.id);
            document.getElementById('employeeForm').setAttribute('data-mode', 'edit');
        }

        function deleteEmployee(id, name) {
            if (confirm(`Are you sure you want to delete ${name} from the system?`)) {
                fetch('delete_employee.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Employee deleted successfully!');
                        loadEmployees(); // Refresh the table
                    } else {
                        alert('Failed to delete employee: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the employee.');
                });
            }
        }

        // Export and refresh functions
        function exportEmployees() {
            alert('Export functionality would generate and download employee data here.');
        }

        function refreshEmployees() {
            loadEmployees();
        }

        // Set default dates in forms
        document.addEventListener('DOMContentLoaded', function() {
            // Set today's date as default for join date
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('joinDate').value = today;
        });
    </script>
</body>
</html>