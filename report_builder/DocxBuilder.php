<?php

class DocxBuilder {
    private $bodyXml = '';
    private $images = [];
    private $relIdCounter = 5;
    private $docRels = [];
    private $contentTypesOverrides = [];

    public function __construct() {
        // Base relationships
        $this->docRels[] = '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';
        $this->docRels[] = '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/header" Target="header1.xml"/>';
        $this->docRels[] = '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/footer" Target="footer1.xml"/>';
    }

    private function escape($str) {
        return htmlspecialchars($str, ENT_XML1, 'UTF-8');
    }

    public function addTitle($text) {
        $text = $this->escape($text);
        $this->bodyXml .= '
        <w:p>
            <w:pPr>
                <w:jc w:val="center"/>
                <w:spacing w:before="360" w:after="180"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>
                    <w:b/>
                    <w:color w:val="1B365D"/>
                    <w:sz w:val="44"/>
                </w:rPr>
                <w:t xml:space="preserve">' . $text . '</w:t>
            </w:r>
        </w:p>';
    }

    public function addSubtitle($text) {
        $text = $this->escape($text);
        $this->bodyXml .= '
        <w:p>
            <w:pPr>
                <w:jc w:val="center"/>
                <w:spacing w:before="120" w:after="240"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>
                    <w:i/>
                    <w:color w:val="455A64"/>
                    <w:sz w:val="28"/>
                </w:rPr>
                <w:t xml:space="preserve">' . $text . '</w:t>
            </w:r>
        </w:p>';
    }

    public function addHeading1($text) {
        $text = $this->escape($text);
        $this->bodyXml .= '
        <w:p>
            <w:pPr>
                <w:pStyle w:val="Heading1"/>
                <w:spacing w:before="360" w:after="140"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>
                    <w:b/>
                    <w:color w:val="1B365D"/>
                    <w:sz w:val="32"/>
                </w:rPr>
                <w:t xml:space="preserve">' . $text . '</w:t>
            </w:r>
        </w:p>';
    }

    public function addHeading2($text) {
        $text = $this->escape($text);
        $this->bodyXml .= '
        <w:p>
            <w:pPr>
                <w:pStyle w:val="Heading2"/>
                <w:spacing w:before="240" w:after="100"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>
                    <w:b/>
                    <w:color w:val="2B579A"/>
                    <w:sz w:val="27"/>
                </w:rPr>
                <w:t xml:space="preserve">' . $text . '</w:t>
            </w:r>
        </w:p>';
    }

    public function addHeading3($text) {
        $text = $this->escape($text);
        $this->bodyXml .= '
        <w:p>
            <w:pPr>
                <w:pStyle w:val="Heading3"/>
                <w:spacing w:before="160" w:after="80"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>
                    <w:b/>
                    <w:color w:val="263238"/>
                    <w:sz w:val="24"/>
                </w:rPr>
                <w:t xml:space="preserve">' . $text . '</w:t>
            </w:r>
        </w:p>';
    }

    public function addParagraph($text, $align = 'both', $bold = false, $italic = false, $spaceAfter = 120) {
        $text = $this->escape($text);
        $bTag = $bold ? '<w:b/>' : '';
        $iTag = $italic ? '<w:i/>' : '';
        $this->bodyXml .= '
        <w:p>
            <w:pPr>
                <w:jc w:val="' . $align . '"/>
                <w:spacing w:before="0" w:after="' . $spaceAfter . '" w:line="276" w:lineRule="auto"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>
                    ' . $bTag . '
                    ' . $iTag . '
                    <w:sz w:val="24"/>
                </w:rPr>
                <w:t xml:space="preserve">' . $text . '</w:t>
            </w:r>
        </w:p>';
    }

    public function addCallout($text, $prefix = 'NOTE:') {
        $text = $this->escape($text);
        $prefix = $this->escape($prefix);
        $this->bodyXml .= '
        <w:p>
            <w:pPr>
                <w:pBdr>
                    <w:left w:val="single" w:sz="24" w:space="12" w:color="1A73E8"/>
                </w:pBdr>
                <w:shd w:val="clear" w:color="auto" w:fill="F0F4F8"/>
                <w:spacing w:before="120" w:after="140" w:line="260" w:lineRule="auto"/>
                <w:ind w:left="360" w:right="240"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>
                    <w:b/>
                    <w:color w:val="1A73E8"/>
                    <w:sz w:val="22"/>
                </w:rPr>
                <w:t xml:space="preserve">' . $prefix . ' </w:t>
            </w:r>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>
                    <w:color w:val="263238"/>
                    <w:sz w:val="22"/>
                </w:rPr>
                <w:t xml:space="preserve">' . $text . '</w:t>
            </w:r>
        </w:p>';
    }

    public function addBullet($text, $boldPrefix = '') {
        $text = $this->escape($text);
        $boldPrefix = $this->escape($boldPrefix);
        
        $bpXml = '';
        if (!empty($boldPrefix)) {
            $bpXml = '<w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>
                    <w:b/>
                    <w:sz w:val="24"/>
                </w:rPr>
                <w:t xml:space="preserve">' . $boldPrefix . ' </w:t>
            </w:r>';
        }

        $this->bodyXml .= '
        <w:p>
            <w:pPr>
                <w:pStyle w:val="ListBullet"/>
                <w:spacing w:before="0" w:after="60" w:line="260" w:lineRule="auto"/>
                <w:ind w:left="540" w:hanging="280"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>
                    <w:color w:val="1B365D"/>
                    <w:sz w:val="24"/>
                </w:rPr>
                <w:t xml:space="preserve">▪  </w:t>
            </w:r>
            ' . $bpXml . '
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>
                    <w:sz w:val="24"/>
                </w:rPr>
                <w:t xml:space="preserve">' . $text . '</w:t>
            </w:r>
        </w:p>';
    }

    public function addImage($imagePath, $caption) {
        if (!file_exists($imagePath)) {
            $this->addCallout("Image missing: $imagePath", "WARNING:");
            return;
        }

        $info = getimagesize($imagePath);
        $origW = $info[0];
        $origH = $info[1];

        // 1 inch = 914,400 EMUs. Target printable width = 5.8 inches = 5,303,520 EMUs
        $targetCx = 5303520;
        $targetCy = (int) round(($targetCx * $origH) / $origW);

        // Cap height at 4.2 inches (3,840,480 EMUs) to ensure page balance
        $maxCy = 3840480;
        if ($targetCy > $maxCy) {
            $targetCy = $maxCy;
            $targetCx = (int) round(($targetCy * $origW) / $origH);
        }

        $relId = 'rIdImg' . ($this->relIdCounter++);
        $imgName = 'image' . ($this->relIdCounter) . '.png';
        
        $this->images[] = [
            'relId' => $relId,
            'sourcePath' => $imagePath,
            'targetName' => $imgName,
            'cx' => $targetCx,
            'cy' => $targetCy
        ];

        $this->docRels[] = '<Relationship Id="' . $relId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/' . $imgName . '"/>';

        $caption = $this->escape($caption);

        $this->bodyXml .= '
        <w:p>
            <w:pPr>
                <w:jc w:val="center"/>
                <w:spacing w:before="180" w:after="80"/>
            </w:pPr>
            <w:r>
                <w:drawing>
                    <wp:inline distT="0" distB="0" distL="0" distR="0">
                        <wp:extent cx="' . $targetCx . '" cy="' . $targetCy . '"/>
                        <wp:effectExtent l="0" t="0" r="0" b="0"/>
                        <wp:docPr id="' . $this->relIdCounter . '" name="' . $imgName . '"/>
                        <wp:cNvGraphicFramePr>
                            <a:graphicFrameLocks xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" noChangeAspect="1"/>
                        </wp:cNvGraphicFramePr>
                        <a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">
                            <a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">
                                <pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">
                                    <pic:nvPicPr>
                                        <pic:cNvPr id="' . $this->relIdCounter . '" name="' . $imgName . '"/>
                                        <pic:cNvPicPr/>
                                    </pic:nvPicPr>
                                    <pic:blipFill>
                                        <a:blip xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" r:embed="' . $relId . '"/>
                                        <a:stretch>
                                            <a:fillRect/>
                                        </a:stretch>
                                    </pic:blipFill>
                                    <pic:spPr>
                                        <a:xfrm>
                                            <a:off x="0" y="0"/>
                                            <a:ext cx="' . $targetCx . '" cy="' . $targetCy . '"/>
                                        </a:xfrm>
                                        <a:prstGeom prst="rect">
                                            <a:avLst/>
                                        </a:prstGeom>
                                    </pic:spPr>
                                </pic:pic>
                            </a:graphicData>
                        </a:graphic>
                    </wp:inline>
                </w:drawing>
            </w:r>
        </w:p>
        <w:p>
            <w:pPr>
                <w:jc w:val="center"/>
                <w:spacing w:before="40" w:after="200"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>
                    <w:i/>
                    <w:b/>
                    <w:color w:val="37474F"/>
                    <w:sz w:val="21"/>
                </w:rPr>
                <w:t xml:space="preserve">' . $caption . '</w:t>
            </w:r>
        </w:p>';
    }

    public function addTable($headers, $rows, $caption = null, $colWidths = []) {
        if (!empty($caption)) {
            $capEsc = $this->escape($caption);
            $this->bodyXml .= '
            <w:p>
                <w:pPr>
                    <w:jc w:val="left"/>
                    <w:spacing w:before="180" w:after="60"/>
                </w:pPr>
                <w:r>
                    <w:rPr>
                        <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>
                        <w:b/>
                        <w:color w:val="1B365D"/>
                        <w:sz w:val="22"/>
                    </w:rPr>
                    <w:t xml:space="preserve">' . $capEsc . '</w:t>
                </w:r>
            </w:p>';
        }

        $numCols = count($headers);
        $totalWidthDxa = 9360; // 6.5 inches printable width
        if (empty($colWidths)) {
            $defColW = (int) ($totalWidthDxa / $numCols);
            $colWidths = array_fill(0, $numCols, $defColW);
        }

        $tblXml = '
        <w:tbl>
            <w:tblPr>
                <w:tblW w:w="' . $totalWidthDxa . '" w:type="dxa"/>
                <w:jc w:val="center"/>
                <w:tblBorders>
                    <w:top w:val="single" w:sz="6" w:space="0" w:color="B0BEC5"/>
                    <w:left w:val="single" w:sz="6" w:space="0" w:color="B0BEC5"/>
                    <w:bottom w:val="single" w:sz="6" w:space="0" w:color="B0BEC5"/>
                    <w:right w:val="single" w:sz="6" w:space="0" w:color="B0BEC5"/>
                    <w:insideH w:val="single" w:sz="4" w:space="0" w:color="CFD8DC"/>
                    <w:insideV w:val="single" w:sz="4" w:space="0" w:color="CFD8DC"/>
                </w:tblBorders>
                <w:tblCellMar>
                    <w:top w:w="120" w:type="dxa"/>
                    <w:left w:w="140" w:type="dxa"/>
                    <w:bottom w:w="120" w:type="dxa"/>
                    <w:right w:w="140" w:type="dxa"/>
                </w:tblCellMar>
            </w:tblPr>
            <w:tblGrid>';
        foreach ($colWidths as $cw) {
            $tblXml .= '<w:gridCol w:w="' . $cw . '"/>';
        }
        $tblXml .= '</w:tblGrid>';

        // Header Row
        $tblXml .= '
            <w:tr>
                <w:trPr>
                    <w:tblHeader/>
                    <w:cantSplit/>
                </w:trPr>';
        foreach ($headers as $idx => $h) {
            $hEsc = $this->escape($h);
            $cw = isset($colWidths[$idx]) ? $colWidths[$idx] : 1500;
            $tblXml .= '
                <w:tc>
                    <w:tcPr>
                        <w:tcW w:w="' . $cw . '" w:type="dxa"/>
                        <w:shd w:val="clear" w:color="auto" w:fill="1B365D"/>
                        <w:vAlign w:val="center"/>
                    </w:tcPr>
                    <w:p>
                        <w:pPr>
                            <w:jc w:val="center"/>
                            <w:spacing w:before="60" w:after="60"/>
                        </w:pPr>
                        <w:r>
                            <w:rPr>
                                <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>
                                <w:b/>
                                <w:color w:val="FFFFFF"/>
                                <w:sz w:val="21"/>
                            </w:rPr>
                            <w:t xml:space="preserve">' . $hEsc . '</w:t>
                        </w:r>
                    </w:p>
                </w:tc>';
        }
        $tblXml .= '</w:tr>';

        // Data Rows
        foreach ($rows as $rIdx => $row) {
            $bgFill = ($rIdx % 2 == 0) ? 'FFFFFF' : 'F7FAFC';
            $tblXml .= '
            <w:tr>
                <w:trPr>
                    <w:cantSplit/>
                </w:trPr>';
            foreach ($row as $cIdx => $cell) {
                $cellEsc = $this->escape($cell);
                $cw = isset($colWidths[$cIdx]) ? $colWidths[$cIdx] : 1500;
                $align = ($cIdx == 0 && count($row) > 4) ? 'center' : 'left';
                $tblXml .= '
                <w:tc>
                    <w:tcPr>
                        <w:tcW w:w="' . $cw . '" w:type="dxa"/>
                        <w:shd w:val="clear" w:color="auto" w:fill="' . $bgFill . '"/>
                        <w:vAlign w:val="center"/>
                    </w:tcPr>
                    <w:p>
                        <w:pPr>
                            <w:jc w:val="' . $align . '"/>
                            <w:spacing w:before="50" w:after="50" w:line="240" w:lineRule="auto"/>
                        </w:pPr>
                        <w:r>
                            <w:rPr>
                                <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>
                                <w:sz w:val="20"/>
                            </w:rPr>
                            <w:t xml:space="preserve">' . $cellEsc . '</w:t>
                        </w:r>
                    </w:p>
                </w:tc>';
            }
            $tblXml .= '</w:tr>';
        }

        $tblXml .= '</w:tbl>';
        $this->bodyXml .= $tblXml;
    }

    public function addPageBreak() {
        $this->bodyXml .= '
        <w:p>
            <w:r>
                <w:br w:type="page"/>
            </w:r>
        </w:p>';
    }

    public function build($outputPath) {
        $zip = new ZipArchive();
        if ($zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            throw new Exception("Unable to create zip file at $outputPath");
        }

        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Default Extension="png" ContentType="image/png"/>
  <Default Extension="jpeg" ContentType="image/jpeg"/>
  <Default Extension="jpg" ContentType="image/jpeg"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
  <Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
  <Override PartName="/word/header1.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.header+xml"/>
  <Override PartName="/word/footer1.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.footer+xml"/>
</Types>';

        $pkgRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>';

        $docRelsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . "\n" . implode("\n", $this->docRels) . "\n</Relationships>";

        $stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:docDefaults>
    <w:rPrDefault>
      <w:rPr>
        <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman" w:eastAsia="Times New Roman"/>
        <w:sz w:val="24"/>
        <w:lang w:val="en-US"/>
      </w:rPr>
    </w:rPrDefault>
    <w:pPrDefault>
      <w:pPr>
        <w:spacing w:line="276" w:lineRule="auto" w:before="0" w:after="120"/>
        <w:jc w:val="both"/>
      </w:pPr>
    </w:pPrDefault>
  </w:docDefaults>
  <w:style w:type="paragraph" w:styleId="Heading1">
    <w:name w:val="heading 1"/>
    <w:rPr>
      <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>
      <w:b/>
      <w:color w:val="1B365D"/>
      <w:sz w:val="32"/>
    </w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Heading2">
    <w:name w:val="heading 2"/>
    <w:rPr>
      <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>
      <w:b/>
      <w:color w:val="2B579A"/>
      <w:sz w:val="27"/>
    </w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Heading3">
    <w:name w:val="heading 3"/>
    <w:rPr>
      <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>
      <w:b/>
      <w:color w:val="263238"/>
      <w:sz w:val="24"/>
    </w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="ListBullet">
    <w:name w:val="List Bullet"/>
    <w:rPr>
      <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>
      <w:sz w:val="24"/>
    </w:rPr>
  </w:style>
</w:styles>';

        $headerXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:hdr xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:p>
    <w:pPr>
      <w:pBdr>
        <w:bottom w:val="single" w:sz="6" w:space="4" w:color="CCCCCC"/>
      </w:pBdr>
      <w:jc w:val="right"/>
      <w:spacing w:after="100"/>
    </w:pPr>
    <w:r>
      <w:rPr>
        <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>
        <w:color w:val="78909C"/>
        <w:sz w:val="18"/>
      </w:rPr>
      <w:t xml:space="preserve">Project Report: Smart HR Connect – Cloud-Based Employee Manager &amp; HR System</w:t>
    </w:r>
  </w:p>
</w:hdr>';

        $footerXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:ftr xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:p>
    <w:pPr>
      <w:pBdr>
        <w:top w:val="single" w:sz="6" w:space="4" w:color="CCCCCC"/>
      </w:pBdr>
      <w:jc w:val="center"/>
      <w:spacing w:before="100"/>
    </w:pPr>
    <w:r>
      <w:rPr>
        <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>
        <w:color w:val="78909C"/>
        <w:sz w:val="18"/>
      </w:rPr>
      <w:t xml:space="preserve">Page </w:t>
    </w:r>
    <w:fldSimple w:instr="PAGE">
      <w:r>
        <w:rPr>
          <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>
          <w:color w:val="78909C"/>
          <w:sz w:val="18"/>
        </w:rPr>
        <w:t>1</w:t>
      </w:r>
    </w:fldSimple>
    <w:r>
      <w:rPr>
        <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>
        <w:color w:val="78909C"/>
        <w:sz w:val="18"/>
      </w:rPr>
      <w:t xml:space="preserve"> of </w:t>
    </w:r>
    <w:fldSimple w:instr="NUMPAGES">
      <w:r>
        <w:rPr>
          <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>
          <w:color w:val="78909C"/>
          <w:sz w:val="18"/>
        </w:rPr>
        <w:t>1</w:t>
      </w:r>
    </w:fldSimple>
  </w:p>
</w:ftr>';

        $documentXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"
            xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"
            xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing"
            xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"
            xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">
  <w:body>
' . $this->bodyXml . '
    <w:sectPr>
      <w:headerReference w:type="default" r:id="rId2"/>
      <w:footerReference w:type="default" r:id="rId3"/>
      <w:pgSz w:w="12240" w:h="15840"/>
      <w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440" w:header="720" w:footer="720" w:gutter="0"/>
    </w:sectPr>
  </w:body>
</w:document>';

        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', $pkgRels);
        $zip->addFromString('word/_rels/document.xml.rels', $docRelsXml);
        $zip->addFromString('word/styles.xml', $stylesXml);
        $zip->addFromString('word/header1.xml', $headerXml);
        $zip->addFromString('word/footer1.xml', $footerXml);
        $zip->addFromString('word/document.xml', $documentXml);

        // Add media files
        foreach ($this->images as $img) {
            $zip->addFile($img['sourcePath'], 'word/media/' . $img['targetName']);
        }

        $zip->close();
    }
}
