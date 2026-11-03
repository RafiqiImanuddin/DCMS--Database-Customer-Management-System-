<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FastXlsxWriter
{
    /**
     * Generate an Excel (.xlsx) file and return a downloadable Laravel response.
     *
     * @param  string  $filename  Nama file yang akan diunduh (misal: data.xlsx)
     * @param  array  $headers  Daftar nama kolom header
     * @param  iterable  $rows  Generator atau array baris data
     */
    public static function download(string $filename, array $headers, iterable $rows): BinaryFileResponse
    {
        $tempDir = sys_get_temp_dir();
        $tempFile = tempnam($tempDir, 'xlsx_');
        self::generate($tempFile, $headers, $rows);

        if (! str_ends_with(strtolower($filename), '.xlsx')) {
            $filename .= '.xlsx';
        }

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-cache, private',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Generate raw OpenXML XLSX zip structure into the target file.
     */
    public static function generate(string $destFilePath, array $headers, iterable $rows): void
    {
        $zip = new \ZipArchive();
        if ($zip->open($destFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("Tidak dapat membuat file XLSX di: {$destFilePath}");
        }

        // 1. [Content_Types].xml
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>');

        // 2. _rels/.rels
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>');

        // 3. xl/_rels/workbook.xml.rels
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>');

        // 4. xl/workbook.xml
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="Data" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>');

        // 5. xl/styles.xml
        // Style 0 = default, Style 1 = Header (Navy background #1E293B, Bold white text), Style 2 = Text (@ format)
        $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="2">'
            .'<font><name val="Calibri"/><sz val="11"/></font>'
            .'<font><b/><color rgb="FFFFFFFF"/><name val="Calibri"/><sz val="11"/></font>'
            .'</fonts>'
            .'<fills count="3">'
            .'<fill><patternFill patternType="none"/></fill>'
            .'<fill><patternFill patternType="gray125"/></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FF1E293B"/></patternFill></fill>'
            .'</fills>'
            .'<borders count="2">'
            .'<border/>'
            .'<border><left style="thin"><color rgb="FFE2E8F0"/></left><right style="thin"><color rgb="FFE2E8F0"/></right><top style="thin"><color rgb="FFE2E8F0"/></top><bottom style="thin"><color rgb="FFE2E8F0"/></bottom></border>'
            .'</borders>'
            .'<cellStyleXfs count="1"><xf/></cellStyleXfs>'
            .'<cellXfs count="3">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/>'
            .'<xf numFmtId="49" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"/>'
            .'</cellXfs>'
            .'</styleSheet>');

        // 6. xl/worksheets/sheet1.xml
        $tempSheet = tempnam(sys_get_temp_dir(), 's1_');
        $s = fopen($tempSheet, 'w');
        fwrite($s, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n");
        fwrite($s, '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'."\n");
        fwrite($s, '<sheetData>'."\n");

        // Row 1: Headers
        $rowIdx = 1;
        fwrite($s, '<row r="'.$rowIdx.'">'."\n");
        foreach ($headers as $cIdx => $header) {
            $colLetter = self::colLetter($cIdx);
            $safe = htmlspecialchars((string) $header, ENT_XML1, 'UTF-8');
            fwrite($s, '<c r="'.$colLetter.$rowIdx.'" s="1" t="inlineStr"><is><t>'.$safe.'</t></is></c>');
        }
        fwrite($s, "\n</row>\n");

        // Data Rows
        foreach ($rows as $row) {
            $rowIdx++;
            fwrite($s, '<row r="'.$rowIdx.'">'."\n");
            $cIdx = 0;
            foreach ($row as $val) {
                $colLetter = self::colLetter($cIdx++);
                if ($val === null || $val === '') {
                    continue;
                }

                // If phone number or text starting with 0/62, format as text
                $strVal = (string) $val;
                if (is_numeric($val) && ! str_starts_with($strVal, '0') && ! str_starts_with($strVal, '62') && strlen($strVal) < 12) {
                    fwrite($s, '<c r="'.$colLetter.$rowIdx.'" t="n"><v>'.(float) $val.'</v></c>');
                } else {
                    $safe = htmlspecialchars($strVal, ENT_XML1, 'UTF-8');
                    fwrite($s, '<c r="'.$colLetter.$rowIdx.'" s="2" t="inlineStr"><is><t>'.$safe.'</t></is></c>');
                }
            }
            fwrite($s, "\n</row>\n");
        }

        fwrite($s, '</sheetData></worksheet>');
        fclose($s);

        $zip->addFile($tempSheet, 'xl/worksheets/sheet1.xml');
        $zip->close();
        @unlink($tempSheet);
    }

    private static function colLetter(int $colIndex): string
    {
        $letter = '';
        while ($colIndex >= 0) {
            $letter = chr($colIndex % 26 + 65).$letter;
            $colIndex = intdiv($colIndex, 26) - 1;
        }

        return $letter;
    }
}
