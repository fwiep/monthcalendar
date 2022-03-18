# MonthCalendar

Generate Dutch 2-page A5 PDF month calendars using PHP

## About

This project generates a 2-page A5 PDF-document containing twelve month
grids of the given year. It uses [mPDF][1] for the PDF dirty work. It is
loosely based on FWiePs [WeekCalendar for PHP][3].

## Example

A month calendar of the year 2023 is part of this project and can be
[downloaded right here][2].

## Installation

To install the script, first clone the repository. Then install the
mPDF-dependency using `composer`. Finally, launch the PHP-server and
open up your browser to generate the document.

```bash
git clone https://github.com/fwiep/monthcalendar.git;
cd monthcalendar;
composer install;
php -S localhost:8080;
```

## Troubleshooting

The only problem I've encountered so far, is that `mPDF` sometimes can't write
to its temporary folder when called through a 'regular' webserver. This can be
fixed by changing the folder's owner and/or permissions. On Fedora, the SELinux
context has to be changed, too.

```bash
cd monthcalendar;
sudo chown -R myuser:apache .;
sudo chmod 775 vendor/mpdf/mpdf/tmp;
sudo chcon --recursive -t httpd_sys_rw_content_t vendor/mpdf/mpdf/tmp/;
```

[1]: https://github.com/mpdf/mpdf
[2]: Maandkalender-2023.pdf
[3]: https://github.com/fwiep/weekcalendar
