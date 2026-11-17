<?php

namespace App\Helpers;

use ZipArchive;
use SimpleXMLElement;

class XlsxHelper
{
    /**
     * Membaca file .xlsx dan mengembalikan array 2D baris dan kolom.
     * Mendukung sharedStrings, inlineStr, direct values, dan sel kosong.
     *
     * @param string $filePath
     * @return array
     */
    public static function read(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \Exception("Gagal membuka file Excel (.xlsx). Pastikan file valid.");
        }

        // 1. Baca shared strings jika ada
        $sharedStrings = [];
        if (($idx = $zip->locateName('xl/sharedStrings.xml')) !== false) {
            $xmlContent = $zip->getFromIndex($idx);
            $xml = simplexml_load_string($xmlContent);
            if ($xml && isset($xml->si)) {
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string)$si->t;
                    } elseif (isset($si->r)) {
                        $part = '';
                        foreach ($si->r as $r) {
                            $part .= (string)($r->t ?? '');
                        }
                        $sharedStrings[] = $part;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // 2. Cari file worksheet utama (sheet1.xml)
        $sheetPath = 'xl/worksheets/sheet1.xml';
        if ($zip->locateName($sheetPath) === false) {
            // Fallback cari file pertama di xl/worksheets/
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (str_starts_with($name, 'xl/worksheets/') && str_ends_with($name, '.xml')) {
                    $sheetPath = $name;
                    break;
                }
            }
        }

        $sheetContent = $zip->getFromName($sheetPath);
        if (!$sheetContent) {
            $zip->close();
            throw new \Exception("Lembar kerja Excel tidak ditemukan.");
        }

        $sheetXml = simplexml_load_string($sheetContent);
        $zip->close();

        if (!$sheetXml || !isset($sheetXml->sheetData)) {
            return [];
        }

        $resultRows = [];

        foreach ($sheetXml->sheetData->row as $row) {
            $rIndex = (int)($row['r'] ?? count($resultRows) + 1);
            $rowData = [];
            $maxCol = 0;

            foreach ($row->c as $cell) {
                $cellRef = (string)$cell['r'];
                $colLetters = preg_replace('/[0-9]/', '', $cellRef);
                $colIndex = self::colLetterToIndex($colLetters);

                $t = (string)$cell['t'];
                $val = '';

                if ($t === 's') {
                    $sIndex = (int)$cell->v;
                    $val = $sharedStrings[$sIndex] ?? '';
                } elseif ($t === 'inlineStr') {
                    $val = (string)($cell->is->t ?? '');
                } else {
                    $val = (string)($cell->v ?? '');
                }

                $rowData[$colIndex] = trim($val);
                if ($colIndex > $maxCol) {
                    $maxCol = $colIndex;
                }
            }

            // Normalisasi array agar indeks 0 sampai maxCol terisi (termasuk sel kosong)
            $normalizedRow = [];
            for ($c = 0; $c <= $maxCol; $c++) {
                $normalizedRow[$c] = $rowData[$c] ?? '';
            }

            $resultRows[] = $normalizedRow;
        }

        return $resultRows;
    }

    /**
     * Membuat file .xlsx murni tanpa dependensi eksternal.
     *
     * @param string $filePath
     * @param array $rows
     * @param string $sheetName
     * @return bool
     */
    public static function create(string $filePath, array $rows, string $sheetName = 'Data'): bool
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return false;
        }

        // [Content_Types].xml
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>');

        // _rels/.rels
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>');

        // xl/workbook.xml
        $cleanSheetName = htmlspecialchars($sheetName, ENT_XML1, 'UTF-8');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="' . $cleanSheetName . '" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>');

        // xl/_rels/workbook.xml.rels
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>');

        // xl/styles.xml (Header tebal + latar abu-abu muda)
        $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <fonts count="2">
        <font><sz val="11"/><name val="Calibri"/></font>
        <font><b/><sz val="11"/><color rgb="FF1E266D"/><name val="Calibri"/></font>
    </fonts>
    <fills count="3">
        <fill><patternFill patternType="none"/></fill>
        <fill><patternFill patternType="gray125"/></fill>
        <fill><patternFill patternType="solid"><fgColor rgb="FFE8ECF8"/><bgColor indexed="64"/></patternFill></fill>
    </fills>
    <borders count="2">
        <border><left/><right/><top/><bottom/><diagonal/></border>
        <border>
            <left style="thin"><color rgb="FFD1D5DB"/></left>
            <right style="thin"><color rgb="FFD1D5DB"/></right>
            <top style="thin"><color rgb="FFD1D5DB"/></top>
            <bottom style="thin"><color rgb="FFD1D5DB"/></bottom>
        </border>
    </borders>
    <cellStyleXfs count="1">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
    </cellStyleXfs>
    <cellXfs count="3">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
        <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/>
        <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/>
    </cellXfs>
</styleSheet>');

        // xl/worksheets/sheet1.xml
        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $sheetXml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n";
        $sheetXml .= '  <sheetData>' . "\n";

        foreach ($rows as $rowIndex => $row) {
            $rNum = $rowIndex + 1;
            $sheetXml .= '    <row r="' . $rNum . '">' . "\n";
            foreach ($row as $colIndex => $val) {
                $cellRef = self::indexToColLetter($colIndex) . $rNum;
                $valEscaped = htmlspecialchars((string)$val, ENT_XML1, 'UTF-8');
                $styleAttr = ($rNum === 1) ? ' s="1"' : ' s="2"';
                $sheetXml .= '      <c r="' . $cellRef . '" t="inlineStr"' . $styleAttr . '><is><t>' . $valEscaped . '</t></is></c>' . "\n";
            }
            $sheetXml .= '    </row>' . "\n";
        }

        $sheetXml .= '  </sheetData>' . "\n";
        $sheetXml .= '</worksheet>';

        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        return true;
    }

    /**
     * Konversi kode huruf kolom Excel ke integer index 0-based (A->0, B->1, AA->26).
     */
    public static function colLetterToIndex(string $col): int
    {
        $col = strtoupper($col);
        $len = strlen($col);
        $idx = 0;
        for ($i = 0; $i < $len; $i++) {
            $idx = $idx * 26 + (ord($col[$i]) - 64);
        }
        return $idx - 1;
    }

    /**
     * Konversi index 0-based ke huruf kolom Excel (0->A, 1->B, 26->AA).
     */
    public static function indexToColLetter(int $index): string
    {
        $letters = '';
        $index++;
        while ($index > 0) {
            $rem = ($index - 1) % 26;
            $letters = chr(65 + $rem) . $letters;
            $index = (int)(($index - $rem) / 26);
        }
        return $letters;
    }
}
