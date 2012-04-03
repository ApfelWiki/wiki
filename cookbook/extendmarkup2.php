<?php if (!defined('PmWiki')) exit();
## Q: and A: markup
## Stellt die Ausgabe von AW1 wieder her.
Markup('F','block','/^([F]):(.*)$/','<p class="q">$2</p>');
Markup('A','block','/^([A]):(.*)$/','<p class="a">$2</p>');

Markup("--",'inline',"/\s--\s/",' &mdash; ');

Markup("<-",'<lsa',"/&lt;-/",'&larr;');
Markup("->",'<rsa',"/([^\n-])(-&gt;)/",'$1&rarr;');

?>