<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$db = new SQLite3('ad_watch.db');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_credentials'])) {
    $new_username = $_POST['username'];
    $new_password = $_POST['password'];
    $user_id = $_SESSION['user_id'];

    $stmt = $db->prepare("UPDATE users SET username = :username, password = :password WHERE id = :id");
    $stmt->bindValue(':username', $new_username, SQLITE3_TEXT);
    $stmt->bindValue(':password', $new_password, SQLITE3_TEXT);
    $stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);

    if ($stmt->execute()) {
        $success = "Credentials updated successfully!";
    } else {
        $error = "Failed to update credentials!";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Ad Watch Dashboard</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <style>
        .total-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
            text-align: center;
        }

        .total-box h4 {
            margin: 0;
            font-size: 1.2em;
        }

        .total-box span {
            font-size: 1.5em;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Daily View</h1>
        <div class="row justify-content-center">
            <div class="col-md-9">
                <canvas id="watchChart"></canvas>
            </div>
            <div class="col-md-3 ">
                <div class="total-box">
                    <h4>Total Males</h4>
                    <span id="dailyMaleCount">0</span>
                </div>
                <div class="total-box">
                    <h4>Total Females</h4>
                    <span id="dailyFemaleCount">0</span>
                </div>
                <div class="total-box">
                    <h4>Total Views</h4>
                    <span id="dailyTotalCount">0</span>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-9">
                <h1>Weekly View</h1>
                <canvas id="weekChart"></canvas>
            </div>
            <div class="col-md-3">
                <div class="total-box">
                    <h4>Total Males</h4>
                    <span id="weeklyMaleCount">0</span>
                </div>
                <div class="total-box">
                    <h4>Total Females</h4>
                    <span id="weeklyFemaleCount">0</span>
                </div>
                <div class="total-box">
                    <h4>Total Views</h4>
                    <span id="weeklyTotalCount">0</span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-9">
                <h1>Monthly View</h1>
                <canvas id="monthChart"></canvas>
            </div>
            <div class="col-md-3">
                <div class="total-box">
                    <h4>Total Males</h4>
                    <span id="monthlyMaleCount">0</span>
                </div>
                <div class="total-box">
                    <h4>Total Females</h4>
                    <span id="monthlyFemaleCount">0</span>
                </div>
                <div class="total-box">
                    <h4>Total Views</h4>
                    <span id="monthlyTotalCount">0</span>
                </div>
            </div>
        </div>
    </div>
    <div class="container w-50 text-center my-5 border border-3">
        <h2>Update Credentials</h2>
        <?php if (isset($success)) : ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)) : ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" action="index.php">
            <div class="form-group row">
                <div class="col">
                    <label for="username">New Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="col">
                    <label for="password">New Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
            </div>
            <button type="submit" name="update_credentials" class="btn btn-primary mt-2">Update</button>
        </form>
        <form method="post" action="logout.php">
            <button type="submit" class="btn btn-danger w-100 mt-5 mb-1">Logout</button>
        </form>
    </div>

    <script>
        function createChart(ctx, type, data, options) {
            return new Chart(ctx, {
                type: type,
                data: data,
                options: options
            });
        }

        function updateChart(chart, data) {
            chart.data.labels = data.labels;
            chart.data.datasets.forEach((dataset, index) => {
                dataset.data = data.datasets[index].data;
            });
            chart.update();
        }

        function fetchData(url, callback) {
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    callback(data);
                })
                .catch(error => {
                    console.error("Error fetching data:", error);
                });
        }

        $(document).ready(function() {
            let watchCtx = document.getElementById('watchChart').getContext('2d');
            let watchChart = createChart(watchCtx, 'line', {
                labels: Array.from({
                    length: 24
                }, (_, i) => `${i}:00`),
                datasets: [{
                        label: 'Male',
                        borderColor: 'blue',
                        data: Array(24).fill(0)
                    },
                    {
                        label: 'Female',
                        borderColor: 'pink',
                        data: Array(24).fill(0)
                    }
                ]
            }, {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            });

            let weekCtx = document.getElementById('weekChart').getContext('2d');
            let weekChart = createChart(weekCtx, 'bar', {
                labels: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                datasets: [{
                        label: 'Male',
                        backgroundColor: 'blue',
                        data: Array(7).fill(0)
                    },
                    {
                        label: 'Female',
                        backgroundColor: 'pink',
                        data: Array(7).fill(0)
                    }
                ]
            }, {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            });

            let monthCtx = document.getElementById('monthChart').getContext('2d');
            let monthChart = createChart(monthCtx, 'bar', {
                labels: Array.from({
                    length: 30
                }, (_, i) => `${i + 1}`),
                datasets: [{
                        label: 'Male',
                        backgroundColor: 'blue',
                        data: Array(30).fill(0)
                    },
                    {
                        label: 'Female',
                        backgroundColor: 'pink',
                        data: Array(30).fill(0)
                    }
                ]
            }, {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            });

            function fetchDayData(day) {
                fetchData(`data.php?day=${day}`, data => {
                    const maleData = Array(24).fill(0);
                    const femaleData = Array(24).fill(0);
                    data.usageByHour.forEach(item => {
                        const hour = parseInt(item.hour.split(':')[0]);
                        maleData[hour] = item.male;
                        femaleData[hour] = item.female;
                    });
                    updateChart(watchChart, {
                        labels: Array.from({
                            length: 24
                        }, (_, i) => `${i}:00`),
                        datasets: [{
                                label: 'Male',
                                borderColor: 'blue',
                                data: maleData
                            },
                            {
                                label: 'Female',
                                borderColor: 'pink',
                                data: femaleData
                            }
                        ]
                    });
                    $('#dailyMaleCount').text(data.totalByDay.totalMale);
                    $('#dailyFemaleCount').text(data.totalByDay.totalFemale);
                    $('#dailyTotalCount').text(data.totalByDay.totalMale + data.totalByDay.totalFemale);
                });
            }

            function fetchWeekData() {
                console.log('Fetching weekly data...');
                fetch('data.php?fetchWeekData=true')
                    .then(response => response.json())
                    .then(data => {
                        console.log('Weekly Data:', data);

                        const daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                        const maleData = Array(7).fill(0);
                        const femaleData = Array(7).fill(0);
                        let totalMale = 0;
                        let totalFemale = 0;

                        data.usageByWeek.forEach(item => {
                            const day = parseInt(item.day);
                            maleData[day] = item.male;
                            femaleData[day] = item.female;
                            totalMale += parseInt(item.male);
                            totalFemale += parseInt(item.female);
                        });

                        const todayIndex = new Date().getDay();

                        const rotatedDays = daysOfWeek.slice(todayIndex + 1).concat(daysOfWeek.slice(0, todayIndex + 1));
                        const rotatedMaleData = maleData.slice(todayIndex + 1).concat(maleData.slice(0, todayIndex + 1));
                        const rotatedFemaleData = femaleData.slice(todayIndex + 1).concat(femaleData.slice(0, todayIndex + 1));

                        console.log('Male Data:', rotatedMaleData);
                        console.log('Female Data:', rotatedFemaleData);

                        updateChart(weekChart, {
                            labels: rotatedDays,
                            datasets: [{
                                    label: 'Male',
                                    backgroundColor: 'blue',
                                    data: rotatedMaleData
                                },
                                {
                                    label: 'Female',
                                    backgroundColor: 'pink',
                                    data: rotatedFemaleData
                                }
                            ]
                        });

                        document.getElementById('weeklyMaleCount').textContent = totalMale;
                        document.getElementById('weeklyFemaleCount').textContent = totalFemale;
                        document.getElementById('weeklyTotalCount').textContent = totalMale + totalFemale;
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                    });
            }





            function fetchMonthData(month) {
                fetchData(`data.php?month=${month}`, data => {
                    const maleData = Array(30).fill(0);
                    const femaleData = Array(30).fill(0);
                    let totalMale = 0;
                    let totalFemale = 0;
                    data.usageByMonth.forEach(item => {
                        const day = parseInt(item.day) - 1;
                        maleData[day] = item.male;
                        femaleData[day] = item.female;
                        totalMale += item.male;
                        totalFemale += item.female;
                    });
                    updateChart(monthChart, {
                        labels: Array.from({
                            length: 30
                        }, (_, i) => `${i + 1}`),
                        datasets: [{
                                label: 'Male',
                                backgroundColor: 'blue',
                                data: maleData
                            },
                            {
                                label: 'Female',
                                backgroundColor: 'pink',
                                data: femaleData
                            }
                        ]
                    });
                    $('#monthlyMaleCount').text(totalMale);
                    $('#monthlyFemaleCount').text(totalFemale);
                    $('#monthlyTotalCount').text(totalMale + totalFemale);
                });
            }

            setInterval(() => {
                fetchDayData(new Date().toISOString().slice(0, 10));
                fetchWeekData(new Date().toISOString().slice(0, 10));
                fetchMonthData(new Date().toISOString().slice(0, 7));
            }, 5000);

            fetchDayData(new Date().toISOString().slice(0, 10));
            fetchWeekData(new Date().toISOString().slice(0, 10));
            fetchMonthData(new Date().toISOString().slice(0, 7));
        });
    </script>
</body>

</html>