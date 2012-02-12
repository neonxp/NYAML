NYAML (NotYAML) - YAML like markup language.
=============

It more clean and (in future :) ) more powerful than YAML.
Author: Alexander "NeonXP" Kiryukhin (frei@neonxp.info)
License: GPLv2

Example of correct NYAML file (test.nyaml):
------
<pre>
<code>
node 1
    #this is node 1
    value 1
    node 2:
        key1: value1
        key2: "value too"
        key3: 'and this!'
    node 3:
        key1: [elements, [of,array]]
        key2: "[this, is, not, array]"
</code>
</pre>
test.php:
------
<pre>
<code>
&lt;?php
    include("nyaml.php");
    $nyaml = new nyaml();
    print_r($nyaml->file("test.nyaml"));
</code>
</pre>
Result:
------
<pre>
<code>
Array
(
    [node 1] => Array
        (
            [0] => value 1
            [node 2] => Array
                (
                    [key1] => value1
                    [key2] => value too
                    [key3] => and this!
                )
            [node 3] => Array
                (
                    [key1] => Array
                        (
                            [0] => elements
                            [1] => Array
                                (
                                    [0] => of
                                    [1] => array
                                )
                        )
                    [key2] => [this, is, not, array]
                )
        )
)
</code>
</pre>