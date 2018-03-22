<?php
//corrige o abbrev das seções Resúmenes de RSM para RSMS

$filename = readline("Digite o nome do arquivo .xml para corrigir as section.abbrev: ");

$xml = new DOMDocument;

if (!$xml->load($filename)) {
        exit("\nNão foi possível abrir o arquivo $filename.\n");
}

$abbrevs = $xml->getElementsByTagName("abbrev");
foreach($abbrevs as $abbrev) {
        //$changeAbbrev = false;
        if ($abbrev->nodeValue === "RSM") {
                $section = $abbrev->parentNode;

                $arrAbbrevs = array();
                $sectionAbbrevs = $section->getElementsByTagName("abbrev");
                foreach($sectionAbbrevs as $sectionAbbrev) {
                        if ($sectionAbbrev->nodeValue === "RSNH") {
                                $abbrev->nodeValue = "RSMS";
                                break;
                        }
                }

                $arrTitles = array();
                $sectionTitles = $section->getElementsByTagName("title");
                foreach($sectionTitles as $sectionTitle) {
                        if ($sectionTitle->parentNode === $section) {
                                array_push($arrTitles, $sectionTitle);
                        }
                }

                $arrAbbrevs = array();
                $sectionAbbrev = $section->getElementsByTagName("abbrev");
                foreach($sectionAbbrev as $sectionAbbrev) {
                        if ($sectionAbbrev->parentNode === $section) {
                                array_push($arrAbbrevs, $sectionAbbrev);
                        }
                }

                $issue = $section->parentNode;
                $volume = $issue->getElementsByTagName("volume")->item(0)->nodeValue;
                $number = $issue->getElementsByTagName("number")->item(0)->nodeValue;
                $year = $issue->getElementsByTagName("year")->item(0)->nodeValue;

                echo "\nedição v. $volume n. $number ($year):\n";
                foreach($arrTitles as $title) {
                        echo "Title locale='" . $title->getAttribute("locale") . "':" . $title->nodeValue . "\n";
                }
                foreach($arrAbbrevs as $abbrev) {
                        echo "Abbrev locale='" . $abbrev->getAttribute("locale") . "':" . $abbrev->nodeValue . "\n";
                }
                echo "\n---------------------------------\n";

        }
}

$newFilename = "corrected_$filename";

if ($xml->save($newFilename)) {
        echo "\nArquivo '$newFilename' com as abbrevs corrigidas criado com sucesso!\n";
}

