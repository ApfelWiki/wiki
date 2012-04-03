<?php if (!defined('PmWiki')) exit();
/*  Copyright 2005 Patrick R. Michaud (pmichaud@pobox.com)
    This file is autorestore.php; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.  

    This script makes it easy to have pages (such as Main.WikiSandbox)
    revert to a "known version" after fifteen minutes of inactivity on
    the page (controlled by $AutoRestoreKeep).  The "known version"
    of pages are held in the wikirev.d/ directory ($AutoRestoreDir).

    To use this script, simply copy it into the cookbook/ directory
    and add the following line to config.php or a per-page/per-group
    customization file.

        include_once('cookbook/autorestore.php');

    Then, create a directory called wikirev.d/, and copy into that
    directory any page files you want to have restored after the
    inactivity interval on that page has elapsed.  (Hint: create 
    or edit the page to contain the text you want restored after 
    each inactivity interval, then copy the appropriate page file 
    from wiki.d/ into wikirev.d/.)

    From then on, any edits to pages in wikirev.d/ will be preserved 
    only as long as another edit is performed within $AutoRestoreKeep 
    seconds.  After that, the edited version of the page is removed, 
    causing PmWiki to default to the original page in wikirev.d/.
*/

# default time to keep pages is 15 minutes (900 seconds)
SDV($AutoRestoreKeep, 900);

# These lines insert the wikirev.d/ directory into the config.
# If the admin wants a custom $AutoRestoreDir, the admin is also
# responsible for setting $WikiLibDirs appropriately.
if (!@$AutoRestoreDir) {
  $AutoRestoreDir = new PageStore('wikirev.d/$FullName');
  array_splice($WikiLibDirs, 1, 0, array($AutoRestoreDir));
}

# If the page doesn't exist in $AutoRestoreDir or $WikiDir, we're done
if (!$AutoRestoreDir->exists($pagename)) return;
if (!$WikiDir->exists($pagename)) return;

# get the current version of the page
$page = ReadPage($pagename);

# if the page is older than $AutoRestoreKeep, delete it
if ($Now - $page['time'] >= $AutoRestoreKeep) 
  $WikiDir->delete($pagename);

?>
