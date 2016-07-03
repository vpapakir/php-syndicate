<?php

/*
 * sock Socket PHP Class
 * Copyright (C) 2010 Evangelos Papakirikou. 
 * sock Socket PHP communication class
 * 
 * Constructor:
 *    new sock(),
 *    new sock(array('address' => address of host,
 *                   'port' => port of host,
 *                   'timeout' (optional),
 *                   'transport', (tcp://, udp://, ssl://, tls://) (ssl and tls
 *                                 not tested)
 *                   ));
 * methods:
 *    bool connect(array('address' => address of host,
 *                   'port' => port of host,
 *                   'timeout' (optional),
 *                   'transport', (tcp://, udp://, ssl://, tls://) (ssl and tls
 *                                 not tested)
 *                   ));
 *    NOTE: connect is called when constructor is called with connection array
 *    bool send_data($data) : sends string $data
 *    bool get_data(&$data) : gets remote data into $data
 *    bool get_timeout() : returns timeout value
 *    bool set_timeout(integer) sets timeout
 *    bool is_conn() returns connect state
 *    void  disconnect();
 */

class sock2 {
    var $con;
    var $parms;
    var $state;
	
	function sock2() {
	}
}

?>