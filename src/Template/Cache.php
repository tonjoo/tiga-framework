<?php 
class Cache  {

    private $options;

    function __construct($options = array()) {
        $this->options = $options
    }

    function getOptions() {
        return array_merge(array(
            'cache_prefix'      =>      'tiga_',
            'cache_ttl'         =>      3600,     // file | apc | memcache
        ), $this->options);
    }

    function setOptions($options = array()) {
        if (isset($options['cache']) && $options['cache']) {
            $this->cache = h2o_cache($options);
        }
    }
    
    function read($filename) {
                
        if (!is_file($filename))
            $filename = $this->get_template_path($this->searchpath,$filename);

        if (is_file($filename)) {
            $source = file_get_contents($filename);
            return $this->runtime->parse($source);
        } else {
            throw new TemplateNotFound($filename);
        }
    }

	function get_template_path($search_path, $filename){

        
        for ($i=0 ; $i < count($search_path) ; $i++) 
        { 
            
            if(file_exists($search_path[$i] . $filename)) {
                $filename = $search_path[$i] . $filename;
                return $filename;
                break;
            } else {
                continue;
            }

        }

        throw new Exception('TemplateNotFound - Looked for template: ' . $filename);

        

	}

    function read_cache($filename) {        
        if (!$this->cache){
             $filename = $this->get_template_path($this->searchpath,$filename);
             return $this->read($filename);
        }
            
        if (!is_file($filename)){
            $filename = $this->get_template_path($this->searchpath,$filename);
        }
            
        $filename = realpath($filename);
        
        $cache = md5($filename);
        $object = $this->cache->read($cache);
        $this->cached = $object && !$this->expired($object);
        
        if (!$this->cached) {
            $nodelist = $this->read($filename);
            $object = (object) array(
                'filename' => $filename,
                'content' => serialize($nodelist),
                'created' => time(),
                'templates' => $nodelist->parser->storage['templates'],
                'included' => $nodelist->parser->storage['included'] + array_values(h2o::$extensions)
            );
            $this->cache->write($cache, $object);
        } else {
            foreach($object->included as $ext => $file) {
                include_once (h2o::$extensions[$ext] = $file);
            }
        }
        return unserialize($object->content);
    }

    function flush_cache() {
        $this->cache->flush();
    }

    function expired($object) {
        if (!$object) return false;
        
        $files = array_merge(array($object->filename), $object->templates);
        foreach ($files as $file) {
            if (!is_file($file))
                $file = $this->get_template_path($this->searchpath, $file);
            
            if ($object->created < filemtime($file))
                return true;
        }
        return false;
    }
}