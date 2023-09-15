<?php
		// Create file
		$qname = "slcmp_4solr-".date("Ymd",time()).".xml";
		$q = fopen($qname,'w');
		fputs($q, $text);
		fclose($q);
		$text ='';
		
		$marr = array();
		require 'metadata.php';
		
		//print_r($marr);
		
		$c2 = 0;
		header("Content-type: text/xml");
		
		$abs_pfad = getcwd();
		$directory = new RecursiveDirectoryIterator('./xml');
		
		$flattened = new RecursiveIteratorIterator($directory);

		// Filetype filtern
		$files = new RegexIterator($flattened, '/^.+\.xml$/i', RecursiveRegexIterator::GET_MATCH);
		
		// Alles Files auflisten
		foreach($files as $file) {
		#if ($c == 10) {break;}
		print $file[0] . PHP_EOL;
		$_file = preg_replace("/.*\\\/","",$file[0]);
		
		// XMLReader start
		$reader = new XMLReader;
		$reader->open($file[0]);
		
		// choose the node element
		while ($reader->read()) {
			if ($reader->nodeType == XMLREADER::ELEMENT AND $reader->localName == "head") {
				$src = $reader->readInnerXML();
				$src = preg_replace("@\s@","",$src);
			}
			if ($reader->nodeType == XMLREADER::ELEMENT AND $reader->localName == "entry") {
				print $c++ . PHP_EOL;
				//if ($c > 5) {break;}
							
					$string = $reader->readOuterXML();
					$xml = simplexml_load_string($string);
					$xml->registerXPathNamespace('tei', 'http://www.tei-c.org/ns/1.0');
		

			$e = $xml->xpath('/*');
			$id = $xml->xpath('@xml:id');
			$id[0]->id = preg_replace("@tasevaGR_KSL@","Syn.Tr._",$id[0]->id);
			$id[0]->id = preg_replace("@tas-gr_ksl@","Syn.Tr._",$id[0]->id);
			$id[0]->id = preg_replace("@^arg-@","Lex.Eccl.-",$id[0]->id);
			
			#print_r($id);
			
			$v = 0;
			$gv = 0;
			$stext .= '<doc>' .PHP_EOL;
			
			$stext .= t2s( $id[0]->id, 'id');
			if (trim($e[0]->note->orig) !='') $stext .= t2s( trim($e[0]->note->orig), 'orig');
			
			for($i=0; $i < count($e[0]->form); $i++) {
				
				
				// hyperlemma
				if ($e[0]->form[$i]->attributes()->type == 'hyperlemma') {
					$stext .= t2s( $e[0]->form[$i]->orth, 'h_lemma');
				}
				
				if ($e[0]->form[$i]->attributes()->type == 'lemma') {		
					// lemma
					$stext .= t2s( $e[0]->form[$i]->orth, 'lemma');
					$stext .= get_gram($e[0]->form[$i], 'lemma_gra'); // lemma grm
					
					// lemma frequency
					if ($e[0]->form[$i]->usg->attributes()->type == 'frequency') {
							$stext .= t2s($e[0]->form[$i]->usg, 'lemma_fre');
						
					} 
					
					if ($e[0]->form[$i]->cit) {
						for($f=0; $f < count($e[0]->form[$i]->cit); $f++) {
							
							for($f2=0; $f2 < count($e[0]->form[$i]->cit[$f]->form); $f2++) {
								if ($e[0]->form[$i]->cit[$f]->form[$f2]->attributes()->type == 'lemma') {
									$stext .= t2s( $e[0]->form[$i]->cit[$f]->form[$f2]->orth, 'lemma_cit-'.$f);
									$stext .= get_biblScope($e[0]->form[$i]->cit[$f]->form[$f2], 'lemma_cit_src-'.$f); // lemma cit biblScope
								}
								if ($e[0]->form[$i]->cit[$f]->form[$f2]->attributes()->type == 'colloc') {
									$stext .= t2s( $e[0]->form[$i]->cit[$f]->form[$f2]->colloc, 'lemma_cit-'.$f);
									$stext .= get_biblScope($e[0]->form[$i]->cit[$f]->form[$f2], 'lemma_cit_src-'.$f); // lemma cit colloc biblScope
								}								
								if ($e[0]->form[$i]->cit[$f]->form[$f2]->attributes()->type == 'hyperlemma') {
									for($l=0; $l < count($e[0]->form[$i]->cit[$f]->form[$f2]->orth); $l++) {
										if ($e[0]->form[$i]->cit[$f]->form[$f2]->orth[$l] !='') $stext .= t2s( $e[0]->form[$i]->cit[$f]->form[$f2]->orth[$l], 'h_lemma_cit-'.$f.'-'.$l);
								}

								$stext .= get_biblScope($e[0]->form[$i]->cit[$f]->form[$f2], 'h_lemma_cit_src-'.$f);	//  lemma biblScope
									
								}
								
								$stext .= get_gram($e[0]->form[$i]->cit[$f]->form[$f2], 'lemma_cit_gra-'.$f2); // lemma cit grm
								
							} 
							
						} // end of cit
					}			
				} // End of attr lemma
				
				
				// variants
				if ($e[0]->form[$i]->attributes()->type == 'variant') {
					
					if (trim($e[0]->form[$i]->note->orig) !='') $stext .= t2s( trim($e[0]->form[$i]->note->orig), 'orig_variant');
					
					if ($e[0]->form[$i]->orth == 'BITTE WORTFORM ERGAENZEN') {$e[0]->form[$i]->orth = 'word form to be completed';}
					
					if ($e[0]->form[$i]->orth !='') {	$stext .= t2s( $e[0]->form[$i]->orth, 'variant-'.$v);}
					
					$stext .= get_gram($e[0]->form[$i], 'variant_gra-'.$v); // variant grm
					
					
					for($f=0; $f < count($e[0]->form[$i]->form); $f++) {
						if ($e[0]->form[$i]->form[$f]->attributes()->type == 'hyperlemma') {
							 $stext .= t2s( arrImp($e[0]->form[$i]->form[$f]->orth), 'h_lemma_variant-'.$gv);	
						}
					// graphical_variant
						if ($e[0]->form[$i]->form[$f]->attributes()->type == 'graphical_variant') {
							 $stext .= t2s( arrImp($e[0]->form[$i]->form[$f]->orth), 'variant_graph-'.$gv); 
									// variant_cits
										for($g=0; $g < count($e[0]->form[$i]->form[$f]->cit); $g++) {
											
											for($f2=0; $f2 < count($e[0]->form[$i]->form[$f]->cit[$g]->form); $f2++) {
												$f3 = 0;
												if ($e[0]->form[$i]->form[$f]->cit[$g]->form[$f2]->attributes()->type == 'lemma') {
													$stext .= t2s( $e[0]->form[$i]->form[$f]->cit[$g]->form[$f2]->orth, 'variant_graph_cit-'.$gv.'-'.$g);
												}
												if ($e[0]->form[$i]->form[$f]->cit[$g]->form[$f2]->attributes()->type == 'colloc') {
													$stext .= t2s( arrImp($e[0]->form[$i]->form[$f]->cit[$g]->form[$f2]->colloc), 'variant_graph_cit-'.$gv.'-'.$g);
														
													$stext .= get_biblScope($e[0]->form[$i]->form[$f]->cit[$g]->form[$f2], 'variant_graph_cit_src-'.$gv.'-'.$g); // biblScope in colloc					
													
												}								
												if ($e[0]->form[$i]->form[$f]->cit[$g]->form[$f2]->attributes()->type == 'hyperlemma') {
													$stext .= t2s( arrImp($e[0]->form[$i]->form[$f]->cit[$g]->form[$f2]->orth), 'h_lemma_variant_graph_cit-'.$gv);
												}

												$stext .= get_biblScope($e[0]->form[$i]->form[$f]->cit[$g]->form[$f2], 'variant_graph_cit_src-'.$gv.'-'.$g); // varant cit biblScope

												$stext .= get_gram($e[0]->form[$i]->form[$f]->cit[$g]->form[$f2], 'variant_graph_cit_gra-'.$gv); // variant cit grm
												
											}
											
										} // end of cit								 
						}
						$gv++;						
					// End graphical_variant	
						
					}
					// variant_cits
						for($f=0; $f < count($e[0]->form[$i]->cit); $f++) {
							
							for($f2=0; $f2 < count($e[0]->form[$i]->cit[$f]->form); $f2++) {
								$f3 = 0;
								if ($e[0]->form[$i]->cit[$f]->form[$f2]->attributes()->type == 'lemma') {
									$stext .= t2s( $e[0]->form[$i]->cit[$f]->form[$f2]->orth, 'variant_cit-'.$v.'-'.$f);
								}
								if ($e[0]->form[$i]->cit[$f]->form[$f2]->attributes()->type == 'colloc') {
									$stext .= t2s( arrImp($e[0]->form[$i]->cit[$f]->form[$f2]->colloc), 'variant_cit-'.$v.'-'.$f);
										
									$stext .= get_biblScope($e[0]->form[$i]->cit[$f]->form[$f2], 'variant_cit_src-'.$v.'-'.$f); // biblScope in colloc					
									
								}								
								if ($e[0]->form[$i]->cit[$f]->form[$f2]->attributes()->type == 'hyperlemma') {
									$stext .= t2s( arrImp($e[0]->form[$i]->cit[$f]->form[$f2]->orth), 'h_lemma_variant_cit-'.$v);
								}

								$stext .= get_biblScope($e[0]->form[$i]->cit[$f]->form[$f2], 'variant_cit_src-'.$v.'-'.$f); // varant cit biblScope

								$stext .= get_gram($e[0]->form[$i]->cit[$f]->form[$f2], 'variant_cit_gra-'.$v); // variant cit grm
								
							}
							
						} // end of cit	
					$stext .= get_biblScope($e[0]->form[$i], 'variant_cit_src-'.$v); // id:Hist.Eccl.-1					
					
				$v++;
				}	// end of variants			
				
				
			} // End of form
			
			$stext .= t2s($src, 'source');
			//print_r($marr[$src]);
			$stext .= t2s(implode(" | ",$marr[$src]), 'metadata');
			$stext .= '</doc>' .PHP_EOL;
			
			#print_r($e);
			
			}	
			} // XMLReader end

		} // End of files
			

		$out = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
		$out .= '<add>' . PHP_EOL;		
		$out .= $stext;
		$out .= '</add>' . PHP_EOL;

		text2file($out,$qname); // write to file

		getchars($out);


		function text2file($text,$qname)
		{
			// Add to fille
			$q = fopen($qname,'a');
			fputs($q, $text);
			fclose($q);
		}
		
		function FormatXML($value) {
			$value = preg_replace('/\040{1,}/',' ',$value);
			$value = htmlspecialchars($value, ENT_COMPAT, "UTF-8");
			return($value);
		}
		
		function t2s($value,$fn) {			
			$text .= '<field name="'.$fn.'">'.FormatXML($value).'</field>' . PHP_EOL;
			return($text);
		}
		
		function arrImp($value) {			
			$arr = json_decode(json_encode((array)$value), TRUE);
			$out = implode(' ', $arr);
			$out = preg_replace("@Array\s@","",$out);
			return($out);
		}	

	function getchars($value){
			$value = preg_replace('/\r\n/m'," ",$value);
			$value = preg_replace('/\t/m'," ",$value);		
			$char = preg_split('//u', $value, null, PREG_SPLIT_NO_EMPTY);
			for ($i = 0; $i < count($char); $i++) {
				if ($char[$i] !='') {$_char[] = $char[$i];}
			}
			#$_char = array_count_values($_char);
			$_char = array_unique($_char);
			asort($_char);
			file_put_contents('charlist.txt',var_export($_char, true));
	}		
	
	
	
	function get_biblScope($arr, $name) {	
			//print_r($arr);
			for($a=0; $a < count($arr->bibl); $a++) {
				if ($arr->bibl[$a]->biblScope) {
					for($i=0; $i < count($arr->bibl[$a]->biblScope); $i++) {
								if ($arr->bibl[$a]->biblScope[$i]->attributes()->unit == 'line') $parr .= '-';
								$parr .= arrImp($arr->bibl[$a]->biblScope[$i]) . ' ';											
					}
				if ($arr->bibl[$a]->note->attributes()->type == 'editors_comment')  $parr .= arrImp($arr->bibl[$a]->note) . ' '; // id:Syn.Tr._1-1380
				}
				$stext .= t2s(trim($parr), $name); $parr = null;
			}
			$stext = preg_replace("@\s(-\d+)@mis","$1",$stext);
			return($stext);
		}
			

	function get_gram($arr, $name) {	
			//print_r($arr);
			for($a=0; $a < count($arr->gramGrp); $a++) {
				if ($arr->gramGrp[$a]->gram) {
					for($i=0; $i < count($arr->gramGrp[$a]->gram); $i++) {
								$parr .= arrImp($arr->gramGrp[$a]->gram[$i]) . ' ‹' . $arr->gramGrp[$a]->gram[$i]->attributes()->type .'›' . ' ';										
						
					}
				}
				$stext .= t2s(trim($parr), $name); $parr = null;
			}
			return($stext);
		}		

						
?>