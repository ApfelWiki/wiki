<?php

  /*
   * To change this template, choose Tools | Templates
   * and open the template in the editor.
   */

  /**
   * Description of PmWikiFileCache
   *
   * @author Schlaefer
   */
  class PmWikiFileCache {

    protected $filename = NULL;
    protected $cachedir = NULL;
    protected $filepath = NULL;
    protected $expires = NULL;

    public function __construct($cachename, $expires = 3600) {
      global $FarmD;
      $this->filename = $cachename;
      $this->cachedir = "$FarmD/cache";
      $this->filepath = $this->cachedir . DIRECTORY_SEPARATOR . $this->filename;
      $this->expires = $expires;
    }

    public function write($data) {
      mkdirp($this->cachedir);
      $saveData = array(
          'meta' => array(
              'savetime' => time(),
          ),
          'data' => $data,
      );
      return file_put_contents($this->filepath, serialize($saveData));
    }

    public function read() {
      $out = FALSE;
      if ( file_exists($this->filepath) ) :
        $readData = unserialize(file_get_contents($this->filepath));
        if ( isset($readData['meta']) && isset($readData['meta']['savetime']) ) :
          if ( $readData['meta']['savetime'] + $this->expires > time() ) :
            $out = $readData['data'];
          endif;
        endif;
      endif;

      return $out;
    }

  }

?>