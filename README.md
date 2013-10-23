# LemCacheContainer - MemCache Api compatible KeyValue-Store written in PHP


## Features
* written completely in PHP
* working with every MemCache client available

## Roadmap
* increasing Performance
* Supporting all MemCache commands


Here's a example how container config in appserver.xml could looks like:

```
<container name="lemcache" threadType="TechDivision\ApplicationServer\ContainerThread"
    type="TechDivision\LemCacheContainer\Container">
    <description><![CDATA[This is an example of a servlet container that uses a socket for sending and receiving HTML requests/responses. This solution can be used for running a web server.]]></description>
    <receiver type="TechDivision\LemCacheContainer\Receiver">
        <worker type="TechDivision\LemCacheContainer\Worker" />
        <thread type="TechDivision\LemCacheContainer\ThreadRequest" />
        <params>
            <param name="workerNumber" type="integer">8</param>
            <param name="address" type="string">0.0.0.0</param>
            <param name="port" type="integer">11210</param>
        </params>
    </receiver>
    <deployment type="TechDivision\LemCacheContainer\Deployment" />
    <host name="localhost" appBase="/webapps" serverAdmin="info@appserver.io"
        serverSoftware="appserver/0.5.8beta1 (linux) PHP/5.5.4">
    </host>
</container>
```