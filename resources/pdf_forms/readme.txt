Instructions for using mikehaertl/php-pdftk to fill in PDF forms
Link: https://github.com/mikehaertl/php-pdftk

TO INSTALL:
- Installing php-pdftk:   sudo snap install pdftk
- Add soft link: sudo ln -s /snap/pdftk/current/usr/bin/pdftk /usr/bin/pdftk
- Git: composer require mikehaertl/php-pdftk

*****************************************************************************
*****************************************************************************
MAPPING:
To determine the names of the fillable fields in the PDF, run:
pdftk path/to/the/form.pdf dump_data_fields > field_names.txt

The output will look something like this:
--
FieldType: Text
FieldName: first_name
FieldFlags: 0
FieldJustification: Left
---
FieldType: Text
FieldName: last_name
FieldFlags: 0
FieldJustification: Left
---

Or the output may look something like this:
---
FieldType: Button
FieldName: topmostSubform[0].CopyA[0].CopyAHeader[0].c1_1[1]
FieldFlags: 1
FieldValue: Off
FieldJustification: Left
FieldStateOption: 2
FieldStateOption: Off
---
FieldType: Text
FieldName: topmostSubform[0].CopyA[0].LeftColumn[0].f1_1[0]
FieldFlags: 8392705
FieldJustification: Left
---

In the latter example, the only way to know which field is what is to use a test program to fill in the form with
junk data and note what data shows up where. For an example of how to fill a form, see
app/Http/Controllers/Admin/Caregiver1099Controller.php

*****************************************************************************
*****************************************************************************
CREATING PDF FROM MULTIPLE PDFs:

use mikehaertl\pdftk\Pdf;

// Extract pages 1-5 and 7,4,9 into a new file
$pdf = new Pdf('/path/to/my.pdf');
$pdf->cat(1, 5)  //range of pages
    ->cat([7, 4, 9]) //individual pages
    ->saveAs('/path/to/new.pdf');


