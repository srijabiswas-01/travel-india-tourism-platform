<?php
require_once __DIR__ . '/../app_bootstrap.php';
require_once __DIR__ . '/../db.php'; require_once __DIR__ . '/../security.php';
function admin_db(): mysqli { return db_connect(); }
function admin_logged_in(): bool { return !empty($_SESSION['admin_id']); }
function require_admin(): void {
    if(!admin_logged_in())redirect_with_message('login.php','Sign in with an administrator account.');
    $routePermissions=['index.php'=>'dashboard.view','regions.php'=>'catalog.manage','packages.php'=>'catalog.manage','hotels.php'=>'catalog.manage','meals.php'=>'catalog.manage','transports.php'=>'catalog.manage','trip-themes.php'=>'catalog.manage','offers.php'=>'catalog.manage','bookings.php'=>'bookings.manage','booking-details.php'=>'bookings.manage','booking-workflow-data.php'=>'bookings.manage','booking-request-action.php'=>'bookings.manage','reviews.php'=>'bookings.manage','notifications.php'=>'notifications.manage','inbox.php'=>'messages.manage','messages.php'=>'messages.manage','customers.php'=>'customers.view','reports.php'=>'reports.view','admins.php'=>'admins.manage'];
    $route=basename($_SERVER['PHP_SELF']??'');
    if(isset($routePermissions[$route])&&!admin_can($routePermissions[$route]))redirect_with_message('index.php','You do not have permission to access that module.');
}
function admin_can(string $permission): bool {
    $role=$_SESSION['admin_role']??'';
    $permissions=[
        'super_admin'=>['*'],
        'manager'=>['dashboard.view','catalog.manage','bookings.manage','notifications.manage','messages.manage','customers.view','customers.manage','reports.view'],
        'booking_admin'=>['dashboard.view','bookings.manage','messages.manage','customers.view'],
        'package_admin'=>['dashboard.view','catalog.manage'],
    ];
    $allowed=$permissions[$role]??[];
    return in_array('*',$allowed,true)||in_array($permission,$allowed,true);
}
function require_admin_permission(string $permission): void { require_admin();if(!admin_can($permission))redirect_with_message('index.php','You do not have permission to access that module.'); }
function admin_audit(mysqli $db,string $action,string $type,string $id='',string $details=''): void { $adminId=$_SESSION['admin_id']??null; $s=$db->prepare('INSERT INTO audit_logs (admin_id,action,entity_type,entity_id,details) VALUES (?,?,?,?,?)'); $s->bind_param('issss',$adminId,$action,$type,$id,$details); $s->execute(); }
function admin_upload_image(string $field,string $folder='catalog'): ?string {
    if(empty($_FILES[$field]['name'])) return null;
    $file=$_FILES[$field];
    if(($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK || ($file['size']??0)>5*1024*1024) throw new RuntimeException('Upload a JPG, PNG, or WebP image smaller than 5 MB.');
    $imageInfo=@getimagesize($file['tmp_name']);$mime=class_exists('finfo')?(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']):($imageInfo['mime']??'');$extensions=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    if(!isset($extensions[$mime]) || !$imageInfo) throw new RuntimeException('The selected file is not a valid image.');
    $directory=__DIR__.'/../uploads/'.$folder;
    if(!is_dir($directory) && !mkdir($directory,0755,true)) throw new RuntimeException('The image upload directory could not be created.');
    $name=hash_file('sha256',$file['tmp_name']).'.'.$extensions[$mime];
    if(!is_file($directory.'/'.$name)&&!move_uploaded_file($file['tmp_name'],$directory.'/'.$name)) throw new RuntimeException('The image could not be saved.');
    return 'uploads/'.$folder.'/'.$name;
}
function admin_remove_unused_image(mysqli $db,?string $path): void {
    if(!$path||!str_starts_with($path,'uploads/'))return;$references=0;
    foreach([['packages','image'],['offers','image'],['states','image'],['countries','image'],['users','profile_image']] as [$table,$column]){$s=$db->prepare("SELECT COUNT(*) total FROM `$table` WHERE `$column`=?");$s->bind_param('s',$path);$s->execute();$references+=(int)$s->get_result()->fetch_assoc()['total'];}
    if($references===0){$full=dirname(__DIR__).'/'.$path;if(is_file($full))@unlink($full);}
}
function admin_paginate(mysqli $db,string $countSql,int $perPage=10,string $parameter='page'): array {
    $total=(int)($db->query($countSql)->fetch_assoc()['total']??0);
    $pages=max(1,(int)ceil($total/$perPage));
    $page=min(max(1,(int)($_GET[$parameter]??1)),$pages);
    return ['total'=>$total,'pages'=>$pages,'page'=>$page,'limit'=>$perPage,'offset'=>($page-1)*$perPage,'parameter'=>$parameter];
}
function admin_pagination(array $pagination): void {
    if($pagination['pages']<=1)return;
    $parameter=$pagination['parameter'];$page=$pagination['page'];$pages=$pagination['pages'];
    echo '<nav class="pagination" aria-label="Table pages">';
    for($i=max(1,$page-2);$i<=min($pages,$page+2);$i++){$query=$_GET;$query[$parameter]=$i;$active=$i===$page?' class="active"':'';echo '<a'.$active.' href="?'.e(http_build_query($query)).'">'.$i.'</a>';}
    echo '</nav>';
}
function admin_search(): string { return trim((string)($_GET['q']??'')); }
function admin_search_condition(mysqli $db,array $columns,string $search): string {
    if($search===''||!$columns)return '';
    $like="'%".$db->real_escape_string($search)."%'";
    return '('.implode(' OR ',array_map(static fn(string $column): string=>$column.' LIKE '.$like,$columns)).')';
}
function admin_directory_tools(string $resetUrl,string $placeholder='Search records',array $hidden=[]): void {
    $search=admin_search();
    echo '<form class="search-form directory-tools" method="get">';
    foreach($hidden as $name=>$value)if($value!==''&&$value!==null)echo '<input type="hidden" name="'.e((string)$name).'" value="'.e((string)$value).'">';
    echo '<label class="sr-only" for="directory-search">'.e($placeholder).'</label><input id="directory-search" type="search" name="q" value="'.e($search).'" placeholder="'.e($placeholder).'">';
    echo '<button class="primary-btn" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button><a class="reset-btn" href="'.e($resetUrl).'"><i class="fa-solid fa-rotate-left"></i> Reset</a></form>';
}
