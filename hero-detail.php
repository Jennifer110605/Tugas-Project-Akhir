<?php
$hero_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$lang = "id";

$url = "https://mlbb-stats.ridwaanhall.com/api/hero-detail/$hero_id/?lang=$lang";
$options = [
    'http' => [
        'header' => "Accept: application/json\r\n"
    ]
];

$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);


// Start HTML output
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hero Detail - Mobile Legends</title>
    <!-- Direct link to hero-detail.css -->
    <link rel="stylesheet" href="assets/css/hero-detail.css">
    <!-- Font Awesome for icons if needed -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

    <!-- Main Content -->
    <main>
        <div class="back-to-forum">
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Kembali ke Forum
            </a>
            <a href="hero-rank.php" class="back-link" style="margin-left: 0.5rem;">
                <i class="fas fa-square-poll-vertical"></i> Peringkat Hero
            </a>
        </div>
        <?php
        if ($response === false) {
            echo '<div class="error-message">Error fetching hero data</div>';
        } else {
            $hero = json_decode($response, true);

            if ($hero['code'] === 0) {
                $heroData = $hero['data']['records'][0]['data'];
                $heroName = $heroData['hero']['data']['name'] ?? 'Unknown Hero';
                $heroStory = $heroData['hero']['data']['story'] ?? 'No story available';
        ?>
                <div class="hero-detail-container">
                    <div class="container">
                        <h1>Informasi Hero</h1>

                        <!-- Hero ID Search Form -->
                        <div class="search-form-container">
                            <h3 class="search-form-title">Cari Hero</h3>
                            <form id="heroSearchForm" class="search-form">
                                <label for="heroNameInput" class="sr-only">Hero Name:</label>
                                <input type="text" id="heroNameInput" name="hero_name" placeholder="Masukkan nama hero (cth: Miya, Tigreal)"
                                    class="search-input" required>
                                <button type="submit" class="search-button">
                                    Search
                                </button>
                            </form>
                            <div id="searchSuggestions" class="search-suggestions"></div>
                        </div>

                        <div class="two-column-layout">
                            <!-- Left Column: Hero Image and Story -->
                            <div class="column-left">
                                <div class="hero-card">
                                    <img src="<?php echo $heroData['head_big'] ?? $heroData['head']; ?>" class="hero-image" alt="<?php echo $heroName; ?>">
                                    <div class="hero-quote">
                                        <h1 class="hero-title"><?php echo $heroName; ?></h1>
                                        <blockquote>
                                            <p class="mb-2"><?php echo $heroStory; ?></p>
                                            <footer class="hero-quote-footer">- <?php echo $heroName; ?></footer>
                                        </blockquote>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Skills and Relationships -->
                            <div class="column-right">
                                <!-- Skills Section -->
                                <div class="section-card">
                                    <div class="section-header">
                                        <h2 class="section-title">Skills</h2>
                                    </div>
                                    <div class="table-container">
                                        <table class="data-table">
                                            <thead>
                                                <tr>
                                                    <th>Icon</th>
                                                    <th>Name</th>
                                                    <th>Description</th>
                                                    <th>CD & Cost</th>
                                                    <th>Tags</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (isset($heroData['hero']['data']['heroskilllist'][0]['skilllist'])):
                                                    foreach ($heroData['hero']['data']['heroskilllist'][0]['skilllist'] as $skill): ?>
                                                        <tr>
                                                            <td>
                                                                <img src="<?php echo $skill['skillicon']; ?>" alt="<?php echo $skill['skillname']; ?>" class="skill-icon">
                                                            </td>
                                                            <td class="skill-name"><?php echo $skill['skillname']; ?></td>
                                                            <td class="skill-description">
                                                                <div class="prose prose-sm prose-invert max-w-none"><?php echo $skill['skilldesc']; ?></div>
                                                            </td>
                                                            <td><?php echo $skill['skillcd&cost'] ?? ''; ?></td>
                                                            <td>
                                                                <?php if (isset($skill['skilltag']) && is_array($skill['skilltag'])):
                                                                    foreach ($skill['skilltag'] as $tag):
                                                                        $rgb = explode(',', $tag['tagrgb']);
                                                                        $bgColor = "rgb({$rgb[0]},{$rgb[1]},{$rgb[2]})";
                                                                ?>
                                                                        <span class="skill-tag" style="background-color: <?php echo $bgColor; ?>;">
                                                                            <?php echo $tag['tagname']; ?>
                                                                        </span>
                                                                <?php endforeach;
                                                                endif; ?>
                                                            </td>
                                                        </tr>
                                                <?php endforeach;
                                                endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Hero Relationships Section -->
                                <?php if (isset($heroData['relation'])): ?>
                                    <div class="section-card">
                                        <div class="section-header">
                                            <h2 class="section-title">Hero Relationships</h2>
                                        </div>
                                        <div class="table-container">
                                            <table class="data-table">
                                                <thead>
                                                    <tr>
                                                        <th>Type</th>
                                                        <th>Hero(es)</th>
                                                        <th>Description</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (isset($heroData['relation']['assist'])): ?>
                                                        <tr>
                                                            <td class="relation-type">Assist</td>
                                                            <td>
                                                                <div class="hero-avatars">
                                                                    <?php foreach ($heroData['relation']['assist']['target_hero'] as $hero):
                                                                        if (isset($hero['data']['head'])): ?>
                                                                            <img src="<?php echo $hero['data']['head']; ?>" alt="Hero"
                                                                                title="<?php echo $hero['data']['name'] ?? ''; ?>">
                                                                    <?php endif;
                                                                    endforeach; ?>
                                                                </div>
                                                            </td>
                                                            <td class="relation-description"><?php echo $heroData['relation']['assist']['desc']; ?></td>
                                                        </tr>
                                                    <?php endif; ?>

                                                    <?php if (isset($heroData['relation']['strong'])): ?>
                                                        <tr>
                                                            <td class="relation-type">Strong Against</td>
                                                            <td>
                                                                <div class="hero-avatars">
                                                                    <?php foreach ($heroData['relation']['strong']['target_hero'] as $hero):
                                                                        if (isset($hero['data']['head'])): ?>
                                                                            <img src="<?php echo $hero['data']['head']; ?>" alt="Hero"
                                                                                title="<?php echo $hero['data']['name'] ?? ''; ?>">
                                                                    <?php endif;
                                                                    endforeach; ?>
                                                                </div>
                                                            </td>
                                                            <td class="relation-description"><?php echo $heroData['relation']['strong']['desc']; ?></td>
                                                        </tr>
                                                    <?php endif; ?>

                                                    <?php if (isset($heroData['relation']['weak'])): ?>
                                                        <tr>
                                                            <td class="relation-type">Weak Against</td>
                                                            <td>
                                                                <div class="hero-avatars">
                                                                    <?php foreach ($heroData['relation']['weak']['target_hero'] as $hero):
                                                                        if (isset($hero['data']['head'])): ?>
                                                                            <img src="<?php echo $hero['data']['head']; ?>" alt="Hero"
                                                                                title="<?php echo $hero['data']['name'] ?? ''; ?>">
                                                                    <?php endif;
                                                                    endforeach; ?>
                                                                </div>
                                                            </td>
                                                            <td class="relation-description"><?php echo $heroData['relation']['weak']['desc']; ?></td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Recommended Builds Section -->
                                <?php if (isset($heroData['hero']['data']['recommendmasterplan'])): ?>
                                    <div class="section-card">
                                        <div class="section-header">
                                            <h2 class="section-title">Recommended Builds</h2>
                                        </div>
                                        <div class="table-container">
                                            <table class="data-table">
                                                <thead>
                                                    <tr>
                                                        <th>Pro Player</th>
                                                        <th>Title</th>
                                                        <th>Battle Spell</th>
                                                        <th>Equipment</th>
                                                        <th>Description</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($heroData['hero']['data']['recommendmasterplan'] as $build): ?>
                                                        <tr>
                                                            <td>
                                                                <div class="player-info">
                                                                    <?php if (isset($build['face'])): ?>
                                                                        <img src="<?php echo $build['face']; ?>" alt="Player" class="player-avatar">
                                                                    <?php endif; ?>
                                                                    <span class="player-name"><?php echo $build['name'] ?? ''; ?></span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="player-title"><?php echo $build['title'] ?? ''; ?></span>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                // Check for battle spell data in the __data subarray
                                                                if (isset($build['battleskill']['__data']['skillicon'])) {
                                                                    $skillIcon = $build['battleskill']['__data']['skillicon'];
                                                                    $skillName = $build['battleskill']['__data']['skillname'] ?? 'Battle Spell';
                                                                    echo '<img src="' . $skillIcon . '" alt="' . $skillName . '" class="battle-skill" title="' . $skillName . '">';
                                                                } else {
                                                                    // Fallback for when no battle spell data is available
                                                                    echo '<span class="text-gray-500">No battle spell</span>';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <div class="item-grid">
                                                                    <?php if (isset($build['equiplist']) && is_array($build['equiplist'])):
                                                                        foreach ($build['equiplist'] as $equip): ?>
                                                                            <img src="<?php echo $equip['equipicon']; ?>"
                                                                                alt="<?php echo $equip['equipname']; ?>"
                                                                                title="<?php echo $equip['equipname']; ?>"
                                                                                class="equipment">
                                                                    <?php endforeach;
                                                                    endif; ?>
                                                                </div>
                                                            </td>
                                                            <td class="relation-description"><?php echo $build['description'] ?? ''; ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
        <?php
            } else {
                echo '<div class="error-message">Error: ' . $hero['message'] . '</div>';
            }
        }
        ?>
    </main>

    <!-- JavaScript -->
    <script>
        document.getElementById('heroSearchForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const heroId = parseInt(document.getElementById('heroIdInput').value, 10);

            if (isNaN(heroId) || heroId < 1 || heroId > 128) {
                alert('Please enter a valid Hero ID between 1 and 128.');
                return;
            }

            window.location.href = 'hero-detail.php?id=' + heroId;
        });
    </script>

    <script>
        // Hero name to ID mapping
        const heroList = {
            "kalea": "128",
            "lukas": "127",
            "suyou": "126",
            "zhuxin": "125",
            "chip": "124",
            "cici": "123",
            "nolan": "122",
            "ixia": "121",
            "arlott": "120",
            "novaria": "119",
            "joy": "118",
            "fredrinn": "117",
            "julian": "116",
            "xavier": "115",
            "melissa": "114",
            "yin": "113",
            "floryn": "112",
            "edith": "111",
            "valentina": "110",
            "aamon": "109",
            "aulus": "108",
            "natan": "107",
            "phoveus": "106",
            "beatrix": "105",
            "gloo": "104",
            "paquito": "103",
            "mathilda": "102",
            "yve": "101",
            "brody": "100",
            "barats": "99",
            "khaleed": "98",
            "benedetta": "97",
            "luo yi": "96",
            "yu zhong": "95",
            "popol and kupa": "94",
            "atlas": "93",
            "carmilla": "92",
            "cecilion": "91",
            "silvanna": "90",
            "wanwan": "89",
            "masha": "88",
            "baxia": "87",
            "lylia": "86",
            "dyrroth": "85",
            "ling": "84",
            "x.borg": "83",
            "terizla": "82",
            "esmeralda": "81",
            "guinevere": "80",
            "granger": "79",
            "khufra": "78",
            "badang": "77",
            "faramis": "76",
            "kadita": "75",
            "minsitthar": "74",
            "harith": "73",
            "thamuz": "72",
            "kimmy": "71",
            "belerick": "70",
            "hanzo": "69",
            "lunox": "68",
            "leomord": "67",
            "vale": "66",
            "claude": "65",
            "aldous": "64",
            "selena": "63",
            "kaja": "62",
            "chang'e": "61",
            "hanabi": "60",
            "uranus": "59",
            "martis": "58",
            "valir": "57",
            "gusion": "56",
            "angela": "55",
            "jawhead": "54",
            "lesley": "53",
            "pharsa": "52",
            "helcurt": "51",
            "zhask": "50",
            "hylos": "49",
            "diggie": "48",
            "lancelot": "47",
            "odette": "46",
            "argus": "45",
            "grock": "44",
            "irithel": "43",
            "harley": "42",
            "gatotkaca": "41",
            "karrie": "40",
            "roger": "39",
            "vexana": "38",
            "lapu-lapu": "37",
            "aurora": "36",
            "hilda": "35",
            "estes": "34",
            "cyclops": "33",
            "johnson": "32",
            "moskov": "31",
            "yi sun-shin": "30",
            "ruby": "29",
            "alpha": "28",
            "sun": "27",
            "chou": "26",
            "kagura": "25",
            "natalia": "24",
            "gord": "23",
            "freya": "22",
            "hayabusa": "21",
            "lolita": "20",
            "minotaur": "19",
            "layla": "18",
            "fanny": "17",
            "zilong": "16",
            "eudora": "15",
            "rafaela": "14",
            "clint": "13",
            "bruno": "12",
            "bane": "11",
            "franco": "10",
            "akai": "9",
            "karina": "8",
            "alucard": "7",
            "tigreal": "6",
            "nana": "5",
            "alice": "4",
            "saber": "3",
            "balmond": "2",
            "miya": "1"
        };

        // Handle form submission
        document.getElementById('heroSearchForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const heroName = document.getElementById('heroNameInput').value.trim().toLowerCase();

            // Try direct match first
            if (heroList[heroName]) {
                window.location.href = 'hero-detail.php?id=' + heroList[heroName];
                return;
            }

            // If no direct match, try partial match
            const matches = [];
            for (const [name, id] of Object.entries(heroList)) {
                if (name.includes(heroName)) {
                    matches.push({
                        name: name,
                        id: id
                    });
                }
            }

            // Show suggestion list if we have partial matches
            const suggestionsDiv = document.getElementById('searchSuggestions');
            suggestionsDiv.innerHTML = '';

            if (matches.length > 0) {
                const heading = document.createElement('h4');
                heading.textContent = 'Did you mean:';
                suggestionsDiv.appendChild(heading);

                const list = document.createElement('ul');
                matches.forEach(match => {
                    const item = document.createElement('li');
                    const link = document.createElement('a');
                    link.href = 'hero-detail.php?id=' + match.id;
                    link.textContent = match.name.charAt(0).toUpperCase() + match.name.slice(1); // Capitalize first letter
                    link.classList.add('suggestion-link');

                    item.appendChild(link);
                    list.appendChild(item);
                });

                suggestionsDiv.appendChild(list);
            } else {
                suggestionsDiv.innerHTML = '<p class="error-text">No hero found with that name. Please try again.</p>';
            }
        });

        // Clear suggestions when input changes
        document.getElementById('heroNameInput').addEventListener('input', function() {
            document.getElementById('searchSuggestions').innerHTML = '';
        });
    </script>
</body>

</html>