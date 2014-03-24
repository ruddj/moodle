<?

/*$services = array(
      'shsmyhomeworkservice' => array(                                                //the name of the web service
          'functions' => array ('local_shsmyhomework_calendar_get'), //web service functions of this service
          'requiredcapability' => '',                //if set, the web service user need this capability to access 
                                                                              //any function of this service. For example: 'some/capability:specified'                 
          'restrictedusers' = >0,                                             //if enabled, the Moodle administrator must link some user to this service
                                                                              //into the administration
          'enabled'=>1,                                                       //if enabled, the service can be reachable on a default installation
       )
  );*/
  
  

$functions = array(
    'local_shsmyhomework_calendar_get' => array(         //web service function name
        'classname'   => 'local_shsmyhomework_external',  //class containing the external function
        'methodname'  => 'calendar_get',          //external function name
        'classpath'   => 'local/shsmyhomework/externallib.php',  //file containing the class/external function
        'description' => 'Fetches calendar information',    //human readable description of the web service function
        'type'        => 'read',                  //database rights of the web service function (read, write)
    ),
    
    'local_shsmyhomework_calendar_create_events' => array(         //web service function name
        'classname'   => 'local_shsmyhomework_external',  //class containing the external function
        'methodname'  => 'calendar_create_events',          //external function name
        'classpath'   => 'local/shsmyhomework/externallib.php',  //file containing the class/external function
        'description' => 'Creates/Updates calendar events',    //human readable description of the web service function
        'type'        => 'write',                  //database rights of the web service function (read, write)
    ),
    
    'local_shsmyhomework_calendar_create' => array(         //web service function name
        'classname'   => 'local_shsmyhomework_external',  //class containing the external function
        'methodname'  => 'calendar_create_events',          //external function name
        'classpath'   => 'local/shsmyhomework/externallib.php',  //file containing the class/external function
        'description' => 'Creates/Updates calendar events',    //human readable description of the web service function
        'type'        => 'write',                  //database rights of the web service function (read, write)
    ),
    
    'local_shsmyhomework_calendar_get_by_id' => array(         //web service function name
        'classname'   => 'local_shsmyhomework_external',  //class containing the external function
        'methodname'  => 'calendar_get_by_id',          //external function name
        'classpath'   => 'local/shsmyhomework/externallib.php',  //file containing the class/external function
        'description' => 'Fetches calendar events by id',    //human readable description of the web service function
        'type'        => 'read',                  //database rights of the web service function (read, write)
    ),
);
