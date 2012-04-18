<?php

/*
 * Zip wrapper
 * Met deze class is het mogelijk om een simpel .zip archief samen te stellen
 * en te laten downloaden. Bestanden kunnen tevens recursief worden toegevoegd.
 */
class ZipWrap 
{
    private $inst = null;
    private $path = null;
    private $open = false;
    private $tmp_file = null;
    private $DownloadName = 'Download.zip';
    
    /*
     * Invoer: (optioneel) map; de map waar in wordt gezocht naar onderliggende bestanden..
     * Effect: De construct functioneert defineert niets anders dan de root map waarin gewerkt wordt.
     * Dit is de map waar de bestanden in worden opgezocht bij de functie add().
     */
    public function __construct($Directory = null)
    {
        if (is_null($Directory))
            $this->path = $_SERVER['DOCUMENT_ROOT'].'/';
        else
            $this->path = $Directory;
    }
    
    /*
     * Effect: Sluit de zip instance. Bij het sluiten zullen de wijzigingen naar de harde
     * schijf worden weggeschreven. Dit is dus tegelijk ook een opslaan functie.
     */
    public function Close()
    {
        // De ZipArchive class in PHP geeft een fout indien er wordt geprobeerd een archief 
        // te sluiten wat al gesloten is. Daarom wordt dit handmatig bijgehouden in $this->open.
        if ($this->open)
        {
            $this->open = false;
            $this->inst->close();
        }
    }
    
    /**
     * create
     * Invoer: optioneel locatie; de locatie van het zip archief in je eigen webserver root.
     * Uitvoer: integer status, geeft aan of het openen gelukt is.
     * Effect: creert een nieuw zip archief.
     */
    public function create($Location = null)
    {
        // Het aanmaken van grote zip bestanden kan lang duren, het is dus handig de tijd limiet
        // op eindig in te stellen.
        // Tevens worden voorgaande archieven gesloten.
        set_time_limit(0);
        $this->close();
        
        // Stel de locatie in. Indien die niet opgegeven is, dan wordt die in de tmp map
        // van het OS aangemaakt. tempnam zorgt er voor dat er een temporary bestand wordt
        // gereserveerd. FDZ is een prefix voor het temp bestand.
        if (is_null($Location))
            $this->tmp_file = tempnam(sys_get_temp_dir(),'FDZ'); // FDZ = prefix
        else
            $this->tmp_file = $Location;
        
        // Creer de Ziparchive instantie en open een zip archief.
        $this->inst = new ZipArchive;
        $Status = $this->inst->open($this->tmp_file, ZipArchive::OVERWRITE);
        
        // Indien dat gelukt is ($Status moet booleaan zijn EN waarde true) , dan open op true zetten.
        if ($Status === TRUE)
            $this->open = true;
        
        return $Status;
        
    }
    
    /**
     * SetName
     * Invoer: Bestandsnaam
     * Effect: Deze functie stelt de download naam in van het bestand. Deze wordt in de headers
     * verwerkt zodat de gebruiker een net bestandsnaam krijgt bij het downloaddialoog van de webbrowser.
     */
    public function SetName($n)
    {
        $this->DownloadName = $n.'.zip';
    }
    
    /*
     * AddPath
     * Invoer: Ref Naam, string key, string path
     * Effect: Deze functie wordt bij add() intern gebruikt, voor array_walk. Voegt bij elk bestandsnaam
     * het juiste pad er aan toe
     */
    private function AddPath(&$Name, $key, $Path)
    {
        $Name = $Path.$Name;
    }
    
    /**
     * addVirtual()
     * Invoer: string bestandsnaam, string inhoud
     * Effect: Voert een 'virtueel' bestand toe. Bij add wordt het bestand van de harde schijf gezocht.
     * Bij deze is het nodig om zelf de inhoud mee te geven.
     */
    public function addVirtual($FileName, $Content)
    {
        $this->addDirectory(dirname($FileName));
        $this->inst->addFromString($FileName, $Content);
    }
    
    /**
     * addDirectory()
     * Invoer: string mapnaam
     * Effect: Voegt een map toe.
     */
    public function addDirectory($Dir)
    {
        if ($Dir == '.')
            return;
        
        $Directories = explode('/',$Dir);
        foreach ($Directories as $i => $dir)
        {
            $DirPath = implode("/", array_slice($Directories, 0, $i)).'/';
            
            
            if ($this->inst->statName($DirPath) === FALSE)
                $this->inst->addEmptyDir(substr($DirPath,0,-1));
        }
    }
    
    /**
     * add()
     * Invoer: string/array bestandsnaam, (meerdere optioneel) string/array bestandsnaam
     * Effect: Voegt de opgegeven bestanden toe aan het zip archief. Deze bestanden staan op de server en worden
     * opgezocht vanaf $this->path die is ingesteld bij het initaliseren van deze klasse.
     * Er kunnen meerdere bestanden worden opgegeven door meerdere strings, een array, meerdere arrays of een combinatie.
     * Deze functie werkt recursief; alle onderliggende bestanden worden dus automatisch meegenomen!
     */
    public function add() 
    {
        if (!$this->open)
            return;
        
        $files = array();
        
        // Doorloop alle ingevoerde argumenten, en voeg de hele reeks toe naar de array files.
        foreach (func_get_args() as $arg)
            if (!is_array($arg))
                $files[] = $arg;
            else
                $files = array_merge($files, $arg);
        
        // Doorloop alle bestanden, of mappen.
        foreach ($files as $file)
        {
            // Indien het een map is,
            
       		
            if (is_dir($this->path . $file .'/'))
            {
                // Creer een lijst met onderliggende bestanden/mappen.
                $SubFiles = array();
                
                $o = opendir($this->path . $file.'/');
                while($f=readdir($o))
                    if($f != '.' && $f != '..')
                        $SubFiles[] = $f;
                
                // Voeg het bestandspad er aan toe..
                array_walk($SubFiles, array($this, 'AddPath'), $file .'/');
                
                // Voeg een lege map toe in het archief, 
                // en alle bestanden door deze functie nog een keer aan te roepen.
                $this->inst->addEmptyDir($file);
                $this->add($SubFiles);
            }
            
            // Indien het een bestand is; gewoon toevoegen.
            // Dit wordt met AddFromString gedaan.
            if (is_file($this->path . $file))
            {
				$this->inst->addFromString($file,file_get_contents($this->path.$file));
               
                
            }
        }
        
    }
    
    /**
     * download
     * Effect: Deze functie bewaard het zip archief en stuurt een reeks headers mee naar de client
     * om de juiste instellingen te krijgen voor het netjes downloaden van een bestand.
     * Indien hier boven al een output is gegeven wordt er een foutmelding gegeven..
     */
    public function download()
    {
    	 if ($this->open)
        {
        	$this->close();
            if (!headers_sent())
            {
                // Deze headers zorgen voor:
                header('Content-Description: File Transfer'); 
                header("Content-Type: application/zip"); // Juiste content type; wat de opgevraagde 'pagina' dus inhoudt.
                header("Content-Disposition: attachment; filename=".$this->DownloadName); // Dat er een bestand wordt gedownload , met als naam...
                header('Content-Transfer-Encoding: binary'); // De gestuurde content een binair formaat is.
                header('Expires: 0'); // Dat deze meteen verloopt.
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header("Content-Length: ".filesize($this->tmp_file)); // De lengte/grootte van het content.
                
                // Weergeef de inhoud van het bestand met readfile.
                readfile($this->tmp_file);
            }
            else
            {
                header("Content-type: text/html");
                trigger_error("Could not send out download of zip file; page headers were sent before!", E_USER_ERROR);
            }
        }
        
    }
    
    /**
     * Effect: Bij het verwijderen van de klasse moet wel netjes het ziparchief worden afgesloten.
     */
    public function __destruct()
    {
        $this->close();
    }
    
}

?>