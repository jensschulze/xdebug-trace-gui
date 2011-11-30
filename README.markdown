Original code from: http://www.rdlt.com/xdebug-trace-file-parser.html

Detects all the traces in your xdebug trace directory and offers to look at them in a nice way.

To enable xdebug:
- install it typing <i>pecl install xdebug</i>
- <i>chwon www-data /var/log/php</i>
- put xdebug.ini at /etc/php5/conf.d 
- <i>service apache2 restart</i>

noutrace.php Xdebug trace gui. With pagination, 1024 sentences in a page. For big traces. Memory and time consumition calculed by difference with previous instruction.

trace.php Xdebug trace gui. Old style, all in a page, with sumaries

graph.php Xdebug trace graph. Statistics memory consumition MB per centesims. You need the parent folder of jpGraph in the include_path.

