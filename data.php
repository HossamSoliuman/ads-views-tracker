<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db.php';

function fetchDataByHour($day)
{
    global $db;
    $query = "SELECT 
                strftime('%H:00', watched_at) as hour, 
                COUNT(CASE WHEN gender = 'male' THEN 1 END) as male, 
                COUNT(CASE WHEN gender = 'female' THEN 1 END) as female 
              FROM ad_watches 
              WHERE date(watched_at) = :day 
              GROUP BY hour 
              ORDER BY hour";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':day', $day, SQLITE3_TEXT);
    $result = $stmt->execute();
    $data = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $data[] = $row;
    }
    return $data;
}

function fetchTotalByDay($day)
{
    global $db;
    $query = "SELECT 
                COUNT(CASE WHEN gender = 'male' THEN 1 END) as totalMale, 
                COUNT(CASE WHEN gender = 'female' THEN 1 END) as totalFemale 
              FROM ad_watches 
              WHERE date(watched_at) = :day";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':day', $day, SQLITE3_TEXT);
    $result = $stmt->execute();
    return $result->fetchArray(SQLITE3_ASSOC);
}
function fetchDataByWeek()
{
    global $db;
    $currentDate = date('Y-m-d');
    $startDate = date('Y-m-d', strtotime($currentDate . ' -6 days'));

    error_log("StartDate: $startDate, EndDate: $currentDate");

    $query = "SELECT 
                strftime('%w', watched_at) as day, 
                COUNT(CASE WHEN gender = 'male' THEN 1 END) as male, 
                COUNT(CASE WHEN gender = 'female' THEN 1 END) as female 
              FROM ad_watches 
              WHERE date(watched_at) BETWEEN :startDate AND :endDate
              GROUP BY day 
              ORDER BY day";

    $stmt = $db->prepare($query);
    $stmt->bindValue(':startDate', $startDate, SQLITE3_TEXT);
    $stmt->bindValue(':endDate', $currentDate, SQLITE3_TEXT);
    $result = $stmt->execute();
    $data = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        error_log("Weekly data: " . json_encode($row)); // Log each row
        $data[] = $row;
    }
    return $data;
}

if (isset($_GET['fetchWeekData'])) {
    $usageByWeek = fetchDataByWeek();
    echo json_encode(['usageByWeek' => $usageByWeek]);
    exit;
}








function fetchDataByMonth($month)
{
    global $db;
    $query = "SELECT 
                strftime('%d', watched_at) as day, 
                COUNT(CASE WHEN gender = 'male' THEN 1 END) as male, 
                COUNT(CASE WHEN gender = 'female' THEN 1 END) as female 
              FROM ad_watches 
              WHERE strftime('%Y-%m', watched_at) = :month 
              GROUP BY day 
              ORDER BY day";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':month', $month, SQLITE3_TEXT);
    $result = $stmt->execute();
    $data = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $data[] = $row;
    }
    return $data;
}

// Function to fetch total count of males and females for all times
function fetchTotalAllTime()
{
    global $db;
    $query = "SELECT 
                COUNT(CASE WHEN gender = 'male' THEN 1 END) as totalMale, 
                COUNT(CASE WHEN gender = 'female' THEN 1 END) as totalFemale 
              FROM ad_watches";
    $result = $db->querySingle($query, true);
    return $result;
}

if (isset($_GET['day'])) {
    $day = $_GET['day'];
    $usageByHour = fetchDataByHour($day);
    $totalByDay = fetchTotalByDay($day);
    echo json_encode(['usageByHour' => $usageByHour, 'totalByDay' => $totalByDay]);
    exit;
}


if (isset($_GET['month'])) {
    $month = $_GET['month'];
    $usageByMonth = fetchDataByMonth($month);
    echo json_encode(['usageByMonth' => $usageByMonth]);
    exit;
}
if (isset($_GET['day'])) {
    $day = $_GET['day'];
    if ($day === 'all') {
        // Fetch total count of males and females for all times
        $totalAllTime = fetchTotalAllTime();
        echo json_encode(['totalByDay' => $totalAllTime]);
        exit;
    } else {
        // Fetch data for a specific day
        $usageByHour = fetchDataByHour($day);
        $totalByDay = fetchTotalByDay($day);
        echo json_encode(['usageByHour' => $usageByHour, 'totalByDay' => $totalByDay]);
        exit;
    }
}
