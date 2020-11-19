<?php
/**
 * Generate a 6x A5 landscape PDF month calendar
 *
 * PHP version 7
 *
 * @category Calendar
 * @package  WeekCalendar
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://fwiep.nl/
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('date.timezone', 'Europe/Amsterdam');
date_default_timezone_set('Europe/Amsterdam');
ini_set('intl.default_locale', 'nl-NL');
setlocale(LC_ALL, array('nl_NL.utf8', 'nl_NL', 'nl', 'dutch', 'nld'));
mb_internal_encoding('UTF-8');

require_once __DIR__ . '/vendor/autoload.php';
$y = (date('Y') + 1);

if ($_POST) {
    if (array_key_exists('inpYear', $_POST) and is_numeric($_POST['inpYear'])) {
        $y = intval($_POST['inpYear']);
    }
    $c = new FWieP\PdfMonthCalendar($y);
    $c->getPDF();
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="utf-8">
<title>PDF maandkalender</title>
</head>
<body>
  <h1>PDF maandkalender</h1>
  <form action="<?php print basename($_SERVER['PHP_SELF'])?>" method="post">
    <fieldset>
      <legend>Kies een jaartal en druk op "Genereren"</legend>

      <div style="margin: 1em 0;">
        <label for="inpYear">Jaar</label> <input type="number"
          name="inpYear" id="inpYear" min="1582" max="3000"
          value="<?php print $y ?>" />
      </div>

      <input type="submit" id="inpSubmit" name="inpSubmit"
        value="Genereren" />

    </fieldset>
  </form>
</body>
</html>