<?php

$string = "ana+maria|stanciu|2900312123321|[usoare_2;cules_struguri;120h30m*10.14/h],[usoare_1;curatit_butuci;30h*10.50/h]|cass5.2%somaj0.5%cas15.8%";


function explodeString(string $string, string $delimiter):array{
    return explode("$delimiter",$string);
}

$array=explodeString($string,"|");

function getLastName(array $array):string{
    return mb_strtoupper(trim($array[1]));
}

function getFirstName(array $array):string{
    $name=mb_convert_case(str_replace("+", " ", $array[0]), 2);
    return $name;
}

function getCnp(array $array):string{
    return $array[2];
}

$lastName=getLastName($array);
$firstName = getFirstName($array);
$cnp = getCnp($array);


$personalInformationArray=[$lastName,$firstName,$cnp];

function formattedPrintPersonalInformation(array $array):string{
    return sprintf(
        "%s|%s %s".PHP_EOL."%s|%s".PHP_EOL,
        str_pad("Nume", 16, " ", STR_PAD_RIGHT),
        $array[0],
        $array[1],
        str_pad("CNP", 16, " ", STR_PAD_RIGHT),
        $array[2]
    );
};

echo formattedPrintPersonalInformation($personalInformationArray).PHP_EOL;


function formattedPrintActivityHeader():string{
    return sprintf(
        "%s|%s|%s|%s|%s",
        str_pad("Cod activitate", 16, " ", 1),
        str_pad("Nume activitate", 19, " ", 1),
        str_pad("Ore", 6, " ", 1),
        "Rata orara",
        str_pad("Suma primita", 13, " ", 0)
        );
}

echo formattedPrintActivityHeader().PHP_EOL;


$activities = explodeString($array[3],",");
$activities = str_replace("[", "", $activities);
$activities = str_replace("]", "", $activities);
natsort($activities);

function getHour(string $string): int
{
    $hourPosition = strpos($string, "h");
    $hours = substr($string, 0, $hourPosition);
    return (int)$hours;
}

function getMinute(string $string): int
{
    $minutePosition = strpos($string, "m");
    if (!$minutePosition) {
        return 0;
    } else {
        $hourPosition = strpos($string, "h");
        $minute = substr($string, $hourPosition + 1, $minutePosition - $hourPosition - 1);
        return (int)$minute;
    }
}

function calculateHourMinute(int $hour, int $minute){
    $minute=(float)$minute;
    $minute/=60;
    $hourMinute=$hour+$minute;
    return $hourMinute;
}

function getHourlyRate(string $string):float{
    $posStar = strpos($string, "*");
    $hourlyRate = substr($string, $posStar + 1);
    $hourlyRate = str_replace("/", "", $hourlyRate);
    $hourlyRate = str_replace("h", "", $hourlyRate);
    return (float)$hourlyRate;
}

$brutRevenue = 0;
setlocale(LC_MONETARY, "ro_RO.UTF-8");

function printActivityLine($activityCode,$activityName,$hours,$hourlyRate,$revenue):string{
    return sprintf(
        "%s|%s|%s|%s|%s",
        str_pad($activityCode, 16, " ", 1),
        str_pad($activityName, 19, " ", 1),
        str_pad(number_format($hours, 1, ',', '.'), 6, " ", 0),
        str_pad(money_format("%.2i", $hourlyRate), 10, " ", 0),
        str_pad(money_format("%.2i", $revenue), 13, " ", 0).PHP_EOL
    );
}

foreach ($activities as $activity) {

    $job = explodeString($activity,";");
    $hours = getHour($job[2]);
    $minute=getMinute($job[2]);
    $hourMinute=calculateHourMinute($hours,$minute);
    $hourlyRate=getHourlyRate($job[2]);
    $revenue = $hourMinute * $hourlyRate;
    $brutRevenue = $brutRevenue + $revenue;

    echo printActivityLine($job[0],$job[1],$hours,$hourlyRate,$revenue);

}


function getContributionPrecent(string $string, string $name, string $replace):float{
    return (float)str_replace($name,$replace,$string);
}

function calculateContribution(float $brutRevenue, float $precent):float{
    return $brutRevenue*$precent/100;
}

$contributions = explodeString($array[4],"%");
$cass=getContributionPrecent($contributions[0],"cass","");
$somaj=getContributionPrecent($contributions[1],"somaj","");
$cas=getContributionPrecent($contributions[2],"cas","");


$valCass = calculateContribution($brutRevenue,$cass);
$valSomaj = calculateContribution($brutRevenue,$somaj);
$valCas = calculateContribution($brutRevenue,$cas);
$totalContributions = $valSomaj + $valCass + $valCas;
$netRevenue = $brutRevenue - $totalContributions;


function printFormattedContributions(string $contribName, string $contribPrecent, float $value,int $padPrecent, int $padMoney):string{
    return sprintf(
        "%s %s|%s",
        $contribName,
        str_pad($contribPrecent, $padPrecent, " ", 0)."%",
        str_pad(money_format("%.2i", $value), $padMoney, " ", 0) . PHP_EOL
    );
}

function printDelimitator():string{
    return str_pad("", 68, "-", 1) . PHP_EOL;
}

function printTotal(string $name,float $revenue):string{
    return $name. str_pad(money_format("%.2i", $revenue), 58, " ", 0) . PHP_EOL;
}


echo printDelimitator();
echo printTotal("TOTAL BRUT",$brutRevenue);
echo PHP_EOL;
echo "Contributii" . PHP_EOL;
echo printDelimitator();

echo printFormattedContributions("CASS",$cass,$valCass,47,15);
echo printFormattedContributions("SOMAJ",$somaj,$valSomaj,46,15);
echo printFormattedContributions("CAS",$cas,$valCas,48,15);

echo printDelimitator();

echo printTotal("TOTAL NET",$netRevenue);
echo PHP_EOL;

