THIS IS A PROTOTYPE
this prototype is for learning how php recives data when piped.
this code is shit, and not final.

these scripts are for parsing snmp trap events.

Running it

listenfortraps.sh | php trapParser.php

this comamnd pipes the data from snmptrapd to the php script wich parses it and spits it into an array for later use.

at the moment it takes the array and parses it further so we can stick it into a database for use in a C# program

The output array for one trap is formatted like this:

Array // wrapper to keep the data together but separate
(
    [0] => Array // Header
        (
            [0] => 2018-04-25 08:47:06 <UNKNOWN> [UDP: [10.0.0.254]:62947->[10.0.0.10]:162]:

        )

    [1] => Array // Content
        (
            [0] => iso.3.6.1.2.1.1.3.0 = Timeticks: (59364891) 6 days, 20:54:08.91
            [1] => iso.3.6.1.6.3.1.1.4.1.0 = OID: iso.3.6.1.4.1.9.9.43.2.0.1
            [2] => iso.3.6.1.4.1.9.9.43.1.1.6.1.3.61 = INTEGER: 1
            [3] => iso.3.6.1.4.1.9.9.43.1.1.6.1.4.61 = INTEGER: 2
            [4] => iso.3.6.1.4.1.9.9.43.1.1.6.1.5.61 = INTEGER: 3

        )

)

To flag a trap for parsing and database sticking action, append this to the getTrapType function

case "trap oid to look for":
    $pos['generic'] = 2;  2 in this case refers to index 2 in the content porsion in the above array
    $pos['specific'] = 5; same here,
    return $pos;



generic and specific are the db column names it should add the data to then inserting into the database.



