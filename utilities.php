<?php
/**
 * Convert date string to a different timezone.
 *
 * @param  string $date     Date value to be converted.
 * @return string|false     Returns the formated date on success or false if date_format() fails.
 */
function convert_date_timezone($date) {
    $newDate = date_create($date, timezone_open('GMT'));
    $tz_date = date_timezone_set($newDate, timezone_open($_SESSION["timezone"]));
    return date_format($tz_date, "h:ia d/m/Y");
}
?>
