<?php
/**
 * Generate a 6x A5 landscape PDF month calendar
 *
 * PHP version 7
 *
 * @category Calendar
 * @package  MonthCalendar
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.fwiep.nl/
 */
namespace FWieP;

use \Mpdf\Output\Destination as D;

/**
 * Generate a 6x A5 landscape PDF month calendar
 *
 * PHP version 7
 *
 * @category Calendar
 * @package  MonthCalendar
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.fwiep.nl/
 */
class PdfMonthCalendar
{
    /**
     * This calendar's year
     *
     * @var integer
     */
    private $_year = 0;

    /**
     * Wraps the given DateTime and adds/subtracts given amount of days
     *
     * @param \DateTime $dt   DateTime to wrap
     * @param int       $days amount of days to add or subtract, can be negative
     *
     * @return \DateTime
     */
    private static function _dtWrap(\DateTime $dt, int $days) : \DateTime
    {
        $outDt = clone $dt;
        $di = new \DateInterval('P'.abs($days).'D');

        if ($days < 0) {
            $outDt->sub($di);
        } else {
            $outDt->add($di);
        }
        return $outDt;
    }

    /**
     * Creates a new month calendar
     *
     * @param int $year the year to display
     */
    public function __construct(int $year)
    {
        if ($year < 1582 || $year > 3000) {
            throw new \InvalidArgumentException(
                "Year should be between 1582 and 3000!"
            );
        }
        $this->_year = $year;
    }

    /**
     * Generates a 52-53 week calendar PDF and outputs it to the browser
     *
     * @return void
     */
    public function getPDF() : void
    {
        $pdfConfig = array(
            'format' => 'A5',
            'margin_left' => 5,
            'margin_right' => 5,
            'margin_top' => 10,
            'margin_bottom' => 5,
            'margin_header' => 5,
            'margin_footer' => 5,
            'orientation' => 'L',
        );
        $pdf = new \Mpdf\Mpdf($pdfConfig);
        $css = file_get_contents('style.css');
        $pdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);

        for ($m = 1; $m <= 12; $m++) {
            $firstThisMonth = new \DateTime($this->_year.'-'.$m.'-01');
            $loopDate = clone $firstThisMonth;

            while ($loopDate->format('N') > 1) {
                $loopDate->sub(new \DateInterval('P1D'));
            }

            $html = '<table>';
            $html .= sprintf(
                '<tr class="title"><th colspan="7">%s %d</th></tr>',
                strftime('%B', $firstThisMonth->getTimestamp()),
                $this->_year
            );
            
            // Array of 8 rows for weeknumbers + weekdays
            $rows = [
                -1 => [], 0 => [], 1 => [], 2 => [],
                3 => [], 4 => [], 5 => [], 6 => []
            ];

            // Additional first row for weeknumbers
            for ($weekLoop = -1; $weekLoop < 6; $weekLoop++) {
                if ($weekLoop == -1) {
                    $rows[-1][] = '<th>wk</th>';
                    continue;
                }
                $dt = self::_dtWrap($firstThisMonth, 7*$weekLoop);
                $rows[-1][] = strftime('<td>%V</td>', $dt->getTimestamp());
            }
            foreach ($rows as $rowIx => &$row) {
                if ($rowIx == -1) {
                    continue;
                }
                for ($colIx = -1; $colIx < 6; $colIx++) {
                    $dt = self::_dtWrap($loopDate, 7*$colIx + $rowIx);
                    if ($colIx == -1) {
                        $row[] = strftime('<th>%a</th>', $dt->getTimestamp());
                        continue;
                    }
                    if ($dt->format('m') != $m) {
                        $row[] = '<td>&nbsp;</td>';
                        continue;
                    }
                    $row[] = strftime('<td>%e</td>', $dt->getTimestamp());
                }
            }
            foreach ($rows as $rowIx => &$row) {
                if ($rowIx == -1) {
                    $html .= '<tr class="week">';
                } else {
                    $html .= '<tr>';
                }
                $html .= implode('', $row);
                $html .= '</tr>';
            }
            $html .= '</table>';
            $pdf->AddPage();
            $pdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
        }
        $pdf->Output('Maandkalender '.$this->_year.'.pdf', D::DOWNLOAD);
    }
}