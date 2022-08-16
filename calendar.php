<?php
function build_calendar($month, $year){
    $mysqli = new mysqli('localhost', 'root', '', 'bookingcalendar');
    
//Array containing all days in a week
$daysOfWeek = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');

// What is the first day of the month in question?
$firstDayOfMonth = mktime(0,0,0,$month,1,$year);

// How many days does this month contain?
$numberDays = date('t',$firstDayOfMonth);

// Retrieve some information about the first day of the
// month in question.
$dateComponents = getdate($firstDayOfMonth);

// What is the name of the month in question?
$monthName = $dateComponents['month'];

// What is the index value (0-6) of the first day of the
// month in question.
$dayOfWeek = $dateComponents['wday'];

// Create the table tag opener and day headers 
$dateToday = date('Y-m-d'); 
$calendar = "<table class='table table-bordered'>"; 
$calendar.= "<center><h2>$monthName $year</h2>"; 
// Previous MOnth
$calendar.= "<a class='btn btn-xs btn-primary' href='?month=".date('m', mktime(0, 0, 0, $month-1, 1, $year))."&year=".date('Y', mktime(0, 0, 0, $month-1, 1, $year))."'>Previous Month</a> ";

// Current Month
$calendar.= "<a class='btn btn-xs btn-primary' href='?month=".date('m')."&year=".date('Y')."'>Current Month</a> ";

// Next Month
$calendar.= "<a href='?month=".date('m', mktime(0, 0, 0, $month+1, 1, $year))."&year=".date('Y', mktime(0, 0, 0, $month+1, 1, $year))."' class='btn btn-xs btn-primary'>Next Month</a></center><br>";

$calendar .= "<tr>"; 
// Create the calendar headers 
foreach($daysOfWeek as $day) { 
     $calendar .= "<th class='header'>$day</th>"; 
} 
// Create the rest of the calendar
// Initiate the day counter, starting with the 1st. 
$calendar.= "</tr><tr>";
// The variable $dayOfWeek is used to 
// ensure that the calendar 
// display consists of exactly 7 columns.
if($dayOfWeek > 0) { 
    for($k=0;$k<$dayOfWeek;$k++){ 
        $calendar .= "<td></td>"; 
    } 
}
$currentDay = 1;
$month = str_pad($month, 2, "0", STR_PAD_LEFT);
while ($currentDay <= $numberDays) { 
    //Seventh column (Saturday) reached. Start a new row. 
    if ($dayOfWeek == 7) { 
        $dayOfWeek = 0; 
        $calendar .= "</tr><tr>"; }
    $currentDayRel = str_pad($currentDay, 2, "0", STR_PAD_LEFT); 
    $date = "$year-$month-$currentDayRel"; 
    $dayname = strtolower(date('l', strtotime($date))); 
    $eventNum = 0; 
    $today = $date==date('Y-m-d')? "today" : "";
    if($dayname=='saturday' || $dayname=='sunday'){
        $calendar.="<td><h4>$currentDay</h4><button class='btn btn-danger btn-xs'>Closed</button>";
    }elseif($date<date('Y-m-d')){
        $calendar.="<td><h4>$currentDay</h4><button class='btn btn-danger btn-xs'>N/A</button>";
    }else{

        $totalbookings = checkSlots($mysqli, $date);
        if($totalbookings==30){
            $calendar.="<td class='$today'><h4>$currentDay</h4><a href='#' class='btn btn-danger btn-xs'>All Booked</a>";
        }else{
            $calendar.="<td class='$today'><h4>$currentDay</h4><a href='book.php?date=".$date."' class='btn btn-success btn-xs'>Book</a><small><i>*Slots Still Open</i></small>";
        }
    }
    
    $calendar.="</td>"; 
    //Increment counters 
    $currentDay++; 
    $dayOfWeek++; 
} 
//Complete the row of the last week in month, if necessary 
if ($dayOfWeek != 7) { 
    $remainingDays = 7 - $dayOfWeek; 
    for($i=0;$i<$remainingDays;$i++){ 
        $calendar .= "<td></td>"; 
    } 
} 

$calendar .= "</tr>"; 
$calendar .= "</table>";
echo $calendar;
}

function checkSlots($mysqli, $date){
    $stmt = $mysqli->prepare("select * from bookings where date = ?");
    $stmt->bind_param('s', $date);
    $totalbookings = 0;
    if($stmt->execute()){
        $result = $stmt->get_result();
        if($result->num_rows>0){
            while($row = $result->fetch_assoc()){
                $totalbookings++;
            }
            
            $stmt->close();
        }
    }

    return $totalbookings;
}

?>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <title>Time Slot Booking</title>

    <style>
       @media only screen and (max-width: 760px),
        (min-device-width: 802px) and (max-device-width: 1020px) {

            /* Force table to not be like tables anymore */
            table, thead, tbody, th, td, tr {
                display: block;

            }
            
            

            .empty {
                display: none;
            }

            /* Hide table headers (but not display: none;, for accessibility) */
            th {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }

            tr {
                border: 1px solid #ccc;
            }

            td {
                /* Behave  like a "row" */
                border: none;
                border-bottom: 1px solid #eee;
                position: relative;
                padding-left: 50%;
            }



            /*
		Label the data
		*/
            td:nth-of-type(1):before {
                content: "Sunday";
            }
            td:nth-of-type(2):before {
                content: "Monday";
            }
            td:nth-of-type(3):before {
                content: "Tuesday";
            }
            td:nth-of-type(4):before {
                content: "Wednesday";
            }
            td:nth-of-type(5):before {
                content: "Thursday";
            }
            td:nth-of-type(6):before {
                content: "Friday";
            }
            td:nth-of-type(7):before {
                content: "Saturday";
            }


        }

        /* Smartphones (portrait and landscape) ----------- */

        @media only screen and (min-device-width: 320px) and (max-device-width: 480px) {
            body {
                padding: 0;
                margin: 0;
            }
        }

        /* iPads (portrait and landscape) ----------- */

        @media only screen and (min-device-width: 802px) and (max-device-width: 1020px) {
            body {
                width: 495px;
            }
        }

        @media (min-width:641px) {
            table {
                table-layout: fixed;
            }
            td {
                width: 33%;
            }
        }
        
        .row{
            margin-top: 20px;
        }
        
        .today{
            background:yellow;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <?php
                    $dateComponents = getdate();
                    if(isset($_GET['month'])&& isset($_GET['year'])){
                        $month = $_GET['month'];
                        $year = $_GET['year'];
                    }else{
                        $month = $dateComponents['mon'];
                        $year = $dateComponents['year'];
                    }

                    echo build_calendar($month,$year);
                ?>
            </div>
        </div>
    </div>
</body>
</html>
