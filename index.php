<?php
set_time_limit(0);

//http://stackoverflow.com/questions/22388647/how-to-use-a-php-library-with-namespacing-without-composer-as-dependency
function __autoload($className)
{
    $className = ltrim($className, '\\');
    $fileName  = '';
    $namespace = '';
    if ($lastNsPos = strripos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
    // $fileName .= $className . '.php'; //sometimes you need a custom structure
    //require_once "library/class.php"; //or include a class manually
    require $fileName;

}


// Create new Plates instance
$templates = new League\Plates\Engine('template');

// Render a template

if(isset($_GET["page"])){
    switch($_GET["page"]){
        case "heatmap":
            echo $templates->render('../page/heatmap', ['page_title' => 'Manpower Allocation']);
            break;
        
        case "threshold":
            echo $templates->render('../page/threshold', ['page_title' => 'Footfall Threshold Setting']);
            break;           
        
        case "userlist":
            echo $templates->render('../page/userlist', ['page_title' => 'Manage User']);
            break; 
        
        case "adduser":
            echo $templates->render('../page/adduser', ['page_title' => 'Add User']);
            break;
        
        case "edituser":
            echo $templates->render('../page/edituser', ['page_title' => 'Edit User']);
            break; 
        
        case "dashboard":
        default:
            echo $templates->render('../page/dashboard', ['page_title' => 'Dashboard']);
            break;
    }
}
else{
    echo $templates->render('../page/dashboard', ['page_title' => 'Dashboard']);
}