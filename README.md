# svc-metaglossar-convert
 <p>SlaVaComp-Metaglossar conversion files.</p>
 
 <h2>Conversion files</h2>
 
<p>the php main script converts a set of files from the present object-oriented XML-TEI format into a relationally oriented Solr-XML format that can be indexed dynamically.</p>
<p>At the same time, the script generates a list of all available characters (Unicode-based letters and the diacritical marks), which can serve as a mapping table for a multilingual and multiscript search.</p>

 <p>The scripts will work with any version of PHP. PHP-CLI 7.2 and higher is recommended.</p>
 <ul>
   <li>svc_process.php - Reads the XML data from the ./xml directory, processes it and saves it as slcmp_4solr-YYYMMDD.xml</li>
   <li>graph_metagls.gv - graphviz file with the structure of data</li>
 </ul>
 
<p>The exact structure of the complex content is mapped in digraph g (which is recorded as a graphviz file).</p>

```
digraph g{ 

	graph [layout = dot , overlap = false, splines=true, rankdir=LR]

	#title [label="SlaVaComp-MGL (TEI-XML -> SOLR)", labelloc=t, fontsize=24];
	
	subgraph cluster_1 {
    lemma -> lemma_hyperlemma
    lemma -> lemma_grm
	}
    lemma -> lemma_cit 
    lemma -> variant 
	
	subgraph cluster_2 {
	lemma_cit -> hyperlemma
			 lemma_cit -> lemma_cit_scr
			 lemma_cit -> lemma_cit_grm
			 }
 
 	subgraph cluster_3 {
	variant -> variant_gra
	variant -> variant_hyperlemma
	variant -> variant_src
	variant -> variant_cit
	subgraph cluster_4 {
			 variant_cit -> variant_cit_scr
			 variant_cit -> variant_cit_grm
			 variant_cit -> variant_cit_hyperlemma
			 }
			 
	}
```
<img src="./gv/graph_metagls.jpg" alt="graph_metagls" width="70%" height="auto">


<p>The SlaVaComp-Metaglossar data are already indexed and can be searched via a <a href="https://slavistik-portal.de/tools/metaglossar/index.html" target="_blank">widget</a> of the Slavistik-Portal (Bootstrap 4.6. based).</p>