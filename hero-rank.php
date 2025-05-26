<?php
// Initial setup and API call to get hero rankings
$days = isset($_GET['days']) ? (int)$_GET['days'] : 1;
$rank = isset($_GET['rank']) ? $_GET['rank'] : 'all';
$size = isset($_GET['size']) ? (int)$_GET['size'] : 20;
$index = isset($_GET['index']) ? (int)$_GET['index'] : 1;
$sort_field = isset($_GET['sort_field']) ? $_GET['sort_field'] : 'win_rate';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'desc';

// Validate inputs
$valid_days = [1, 3, 7, 15, 30];
$valid_ranks = ['all', 'epic', 'legend', 'mythic', 'honor', 'glory'];
$valid_sort_fields = ['pick_rate', 'ban_rate', 'win_rate'];
$valid_sort_orders = ['asc', 'desc'];

// Ensure parameters are valid
if (!in_array($days, $valid_days)) $days = 1;
if (!in_array($rank, $valid_ranks)) $rank = 'all';
if ($size < 1 || $size > 127) $size = 20;
if ($index < 1 || $index > 127) $index = 1;
if (!in_array($sort_field, $valid_sort_fields)) $sort_field = 'win_rate';
if (!in_array($sort_order, $valid_sort_orders)) $sort_order = 'desc';

// Build API URL
$api_url = "https://mlbb-stats.ridwaanhall.com/api/hero-rank/";
$api_url .= "?days=$days&rank=$rank&size=$size&index=$index&sort_field=$sort_field&sort_order=$sort_order";

// Function to fetch data from API
function fetchApiData($url)
{
    try {
        $opts = [
            'http' => [
                'method' => "GET",
                'header' => "Accept: application/json\r\n",
                'timeout' => 30
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ];

        $context = stream_context_create($opts);
        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            return false;
        }

        return json_decode($response, true);
    } catch (Exception $e) {
        return false;
    }
}

// Get hero ranking data
$heroRankData = fetchApiData($api_url);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hero Ranking - Mobile Legends</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/hero-detail.css">
    <style>
        .filter-container {
            background-color: #1f2937;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-label {
            font-size: 0.875rem;
            color: #d1d5db;
        }

        .filter-select {
            background-color: #374151;
            color: #e5e7eb;
            border: 1px solid #4b5563;
            border-radius: 0.25rem;
            padding: 0.5rem;
        }

        .rank-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .rank-table th {
            background-color: #374151;
            color: #e5e7eb;
            padding: 0.75rem 1rem;
            text-align: left;
            font-size: 0.875rem;
        }

        .rank-table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #374151;
        }

        .hero-cell {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .hero-image {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .hero-name {
            font-weight: 500;
            color: #e5e7eb;
        }

        .hero-name a {
            color: #e5e7eb;
            text-decoration: none;
        }

        .hero-name a:hover {
            color: #93c5fd;
            text-decoration: underline;
        }

        .sub-heroes {
            display: flex;
            gap: 0.25rem;
        }

        .sub-hero-image {
            width: 25px;
            height: 25px;
            border-radius: 50%;
            object-fit: cover;
            transition: transform 0.2s;
        }

        .sub-hero-image:hover {
            transform: scale(1.2);
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 1.5rem;
            gap: 0.25rem;
        }

        .pagination a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.25rem;
            background-color: #374151;
            color: #e5e7eb;
            text-decoration: none;
        }

        .pagination a.active {
            background-color: #2563eb;
            font-weight: bold;
        }

        .pagination a:hover:not(.active) {
            background-color: #4b5563;
        }

        .percentage {
            font-family: monospace;
            font-weight: 500;
        }

        .center-align {
            text-align: center;
        }

        .rank-note {
            color: #9ca3af;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            text-align: center;
        }
    </style>
</head>

<body>
    <main>
        <!-- Back to Forum Link -->
        <div class="back-to-forum">
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Kembali ke Forum
            </a>

            <a href="hero-detail.php" class="back-link" style="margin-left: 0.5rem;">
                <i class="fas fa-info"></i> Detail Hero
            </a>
        </div>

        <div class="hero-detail-container">
            <div class="container">
                <h1 class="hero-title">Peringkat Hero Mobile Legends</h1>

                <!-- Filter Form -->
                <div class="filter-container">
                    <form class="filter-form" method="get" action="hero-rank.php">
                        <div class="filter-group">
                            <label class="filter-label">Periode</label>
                            <select name="days" class="filter-select">
                                <option value="1" <?php echo $days == 1 ? 'selected' : ''; ?>>1 Hari</option>
                                <option value="3" <?php echo $days == 3 ? 'selected' : ''; ?>>3 Hari</option>
                                <option value="7" <?php echo $days == 7 ? 'selected' : ''; ?>>7 Hari</option>
                                <option value="15" <?php echo $days == 15 ? 'selected' : ''; ?>>15 Hari</option>
                                <option value="30" <?php echo $days == 30 ? 'selected' : ''; ?>>30 Hari</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Rank</label>
                            <select name="rank" class="filter-select">
                                <option value="all" <?php echo $rank == 'all' ? 'selected' : ''; ?>>Semua</option>
                                <option value="epic" <?php echo $rank == 'epic' ? 'selected' : ''; ?>>Epic</option>
                                <option value="legend" <?php echo $rank == 'legend' ? 'selected' : ''; ?>>Legend</option>
                                <option value="mythic" <?php echo $rank == 'mythic' ? 'selected' : ''; ?>>Mythic</option>
                                <option value="honor" <?php echo $rank == 'honor' ? 'selected' : ''; ?>>Honor</option>
                                <option value="glory" <?php echo $rank == 'glory' ? 'selected' : ''; ?>>Glory</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Urutkan Berdasarkan</label>
                            <select name="sort_field" class="filter-select">
                                <option value="win_rate" <?php echo $sort_field == 'win_rate' ? 'selected' : ''; ?>>Win Rate</option>
                                <option value="pick_rate" <?php echo $sort_field == 'pick_rate' ? 'selected' : ''; ?>>Pick Rate</option>
                                <option value="ban_rate" <?php echo $sort_field == 'ban_rate' ? 'selected' : ''; ?>>Ban Rate</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Urutan</label>
                            <select name="sort_order" class="filter-select">
                                <option value="desc" <?php echo $sort_order == 'desc' ? 'selected' : ''; ?>>Tertinggi</option>
                                <option value="asc" <?php echo $sort_order == 'asc' ? 'selected' : ''; ?>>Terendah</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Tampilkan</label>
                            <select name="size" class="filter-select">
                                <option value="10" <?php echo $size == 10 ? 'selected' : ''; ?>>10</option>
                                <option value="20" <?php echo $size == 20 ? 'selected' : ''; ?>>20</option>
                                <option value="50" <?php echo $size == 50 ? 'selected' : ''; ?>>50</option>
                                <option value="100" <?php echo $size == 100 ? 'selected' : ''; ?>>100</option>
                            </select>
                        </div>

                        <div class="filter-group" style="justify-content: flex-end;">
                            <button type="submit" class="search-button" style="margin-top: 1.5rem;">
                                <i class="fas fa-filter"></i> Terapkan Filter
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Hero Rank Table -->
                <div class="section-card">
                    <div class="section-header">
                        <h2 class="section-title">
                            Peringkat Hero
                            <?php if ($rank != 'all'): ?>
                                <span style="font-size: 0.875rem; font-weight: normal; color: #9ca3af;"> - Rank <?php echo ucfirst($rank); ?></span>
                            <?php endif; ?>
                        </h2>
                    </div>
                    <div class="section-content">
                        <?php if ($heroRankData && isset($heroRankData['data']) && isset($heroRankData['data']['records'])): ?>
                            <div class="table-container">
                                <table class="rank-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 60px;">Rank</th>
                                            <th>Hero</th>
                                            <th style="width: 120px;">Win Rate</th>
                                            <th style="width: 120px;">Pick Rate</th>
                                            <th style="width: 120px;">Ban Rate</th>
                                            <th>Pasangan Terbaik</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $records = $heroRankData['data']['records'];
                                        $startRank = ($index - 1) * $size + 1;

                                        foreach ($records as $i => $record):
                                            $mainHero = $record['data']['main_hero']['data'];
                                            $winRate = $record['data']['main_hero_win_rate'] * 100;
                                            $pickRate = $record['data']['main_hero_appearance_rate'] * 100;
                                            $banRate = $record['data']['main_hero_ban_rate'] * 100;
                                            $subHeroes = $record['data']['sub_hero'];
                                        ?>
                                            <tr>
                                                <td class="center-align"><?php echo $startRank + $i; ?></td>
                                                <td>
                                                    <div class="hero-cell">
                                                        <img src="<?php echo $mainHero['head']; ?>" alt="<?php echo $mainHero['name']; ?>" class="hero-image">
                                                        <span class="hero-name">
                                                            <a href="hero-detail.php?id=<?php echo $record['data']['main_heroid']; ?>">
                                                                <?php echo $mainHero['name']; ?>
                                                            </a>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="percentage"><?php echo number_format($winRate, 2); ?>%</td>
                                                <td class="percentage"><?php echo number_format($pickRate, 2); ?>%</td>
                                                <td class="percentage"><?php echo number_format($banRate, 2); ?>%</td>
                                                <td>
                                                    <div class="sub-heroes">
                                                        <?php foreach ($subHeroes as $subHero): ?>
                                                            <div class="sub-hero-item" title="<?php echo number_format($subHero['increase_win_rate'] * 100, 2); ?>% win rate increase">
                                                                <a href="hero-detail.php?id=<?php echo $subHero['heroid']; ?>">
                                                                    <img src="<?php echo $subHero['hero']['data']['head']; ?>" alt="Hero" class="sub-hero-image">
                                                                </a>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php
                            $total = $heroRankData['data']['total'];
                            $totalPages = ceil($total / $size);
                            $maxPagesToShow = 5;
                            ?>

                            <?php if ($totalPages > 1): ?>
                                <div class="pagination">
                                    <!-- Previous button -->
                                    <?php if ($index > 1): ?>
                                        <a href="hero-rank.php?days=<?php echo $days; ?>&rank=<?php echo $rank; ?>&size=<?php echo $size; ?>&index=<?php echo $index - 1; ?>&sort_field=<?php echo $sort_field; ?>&sort_order=<?php echo $sort_order; ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    <?php endif; ?>

                                    <!-- Page numbers -->
                                    <?php
                                    $startPage = max(1, min($index - floor($maxPagesToShow / 2), $totalPages - $maxPagesToShow + 1));
                                    $endPage = min($startPage + $maxPagesToShow - 1, $totalPages);

                                    for ($i = $startPage; $i <= $endPage; $i++):
                                        $active = $i == $index ? 'active' : '';
                                    ?>
                                        <a href="hero-rank.php?days=<?php echo $days; ?>&rank=<?php echo $rank; ?>&size=<?php echo $size; ?>&index=<?php echo $i; ?>&sort_field=<?php echo $sort_field; ?>&sort_order=<?php echo $sort_order; ?>" class="<?php echo $active; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>

                                    <!-- Next button -->
                                    <?php if ($index < $totalPages): ?>
                                        <a href="hero-rank.php?days=<?php echo $days; ?>&rank=<?php echo $rank; ?>&size=<?php echo $size; ?>&index=<?php echo $index + 1; ?>&sort_field=<?php echo $sort_field; ?>&sort_order=<?php echo $sort_order; ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <p class="rank-note">Data berdasarkan statistik <?php echo $days; ?> hari terakhir</p>

                        <?php else: ?>
                            <div class="error-message">
                                <p>Gagal memuat data peringkat hero. Silakan coba lagi nanti.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- JavaScript for tooltips or other interactions can be added here -->
    <script>
        // Auto-submit form when any select changes
        document.querySelectorAll('.filter-select').forEach(select => {
            select.addEventListener('change', function() {
                this.form.submit();
            });
        });
    </script>
</body>

</html>