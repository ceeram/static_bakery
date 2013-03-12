

Upload simple & miniatures
==========================

by %s on July 06, 2010

Composant permettant l'upload de fichier avec gÃ©nÃ©ration de
miniatures pour les images. Gestion des actions : - supprimer -
tÃ©lÃ©charger - stockage des lien dans la base de donnÃ©es
[code] class UploadComponent extends Object
{
/**
* Upload component
*
*
* @writtenby G. Tenthorey
* @lastmodified 06.07.2010
* @license free
*/

var $controller;

function startup(&$controller){
$this->controller = &$controller;
}

/*
* Upload un fichier sur le serveur
* @param array ($this->data)
* @param string (nom du modÃ¨le pour sauver les fichier)
* @param string (rep d'upload par dÃ©faut -> webroot)
* @param array (extension acceptÃ©es)
* @param int (taille maximale du fichier en Mo dÃ©faut -> 2mo)
* @param bool (gÃ©nÃ©rer automatiquement des miniatures dÃ©faut ->
activÃ©)
* @return -
**/
function fichier($data,$model,$upload_rep,array
$ext_valide,$taille_max_mo=2,$thumb=1){

// Stock les erreurs pour les passer Ã la vue
$erreurs = array();

if(!empty($data[$model]['filename']['name'])){

// Config
$err = 0;
$taille_max_o = $taille_max_mo*1048576;
$upload_rep_fichier = $upload_rep.$data[$model]['filename']['name'];
$taille_fichier = $data[$model]['filename']['size'];
$tmp_name = $data[$model]['filename']['tmp_name'];
$nom_fichier = $data[$model]['filename']['name'];
$extension = strrchr($data[$model]['filename']['name'],'.');

// On crÃ©e le rÃ©pertoire de destination s'il existe pas
if(!file_exists($upload_rep)){
mkdir($upload_rep,0755,true);
}

// Type de fichier acceptÃ©s
if(!in_array($extension,$ext_valide)){
$erreurs[] = __('L\'extension du fichier n\'est pas valide.',true);
$err = 1;
}

// Taille maximal acceptÃ©e
if($taille_fichier>$taille_max_o){
$erreurs[] = __('Le fichier est trop grand.',true).' (max
'.$taille_max_mo.' Mo)';
$err = 1;
}

// Si un fichier du mÃªme nom existe dÃ©jÃ on gÃ©nÃ¨re un nouveau nom
if(file_exists($upload_rep_fichier)){
$nom_fichier_sans_extension = basename($nom_fichier,$extension);
$upload_rep_fichier =
$upload_rep.$nom_fichier_sans_extension.'_'.sha1(uniqid()).$extension;
$nom_fichier =
$nom_fichier_sans_extension.'_'.sha1(uniqid()).$extension;
}

// Si le modÃ¨le est valide on dÃ©place le fichier
App::Import('Model',$model);
$Fichier = new $model();

$Fichier->set($data);
$Fichier->validates();
$erreurs_models = $Fichier->invalidFields();
if(!empty($erreurs_models)){
$err = 1;
}

if($err==0){
// Si un soucis survient lors du dÃ©placement du fichier
if(!move_uploaded_file($tmp_name,$upload_rep_fichier)){
$erreurs[] = __('Le fichier n\'a pas Ã©tÃ© transfÃ©rÃ©
correctement.',true);
$err = 1;
return false;
// Si tout est en rÃ¨gle on dÃ©place le fichier
}else{

$data[$model]['libelle'] = $data[$model]['libelle'];
$data[$model]['nom_fichier'] = $nom_fichier;
$data[$model]['url_fichier'] = $upload_rep_fichier;
$data[$model]['url_rep'] = $upload_rep;
$data[$model]['extension'] = $extension;
$data[$model]['taille'] = $taille_fichier;
$Fichier->save($data);

// On crÃ©e des miniatures
App::import('Vendor', 'phpthumb', array('file' =>
'ThumbLib.inc.php'));
if(in_array($extension,array('.png','.jpg','.gif','.jpeg')) &&
$thumb==1){
// Large
if(!file_exists($upload_rep.'/thumb_small')){mkdir($upload_rep.'/thumb
_small',0755);}
$thumb = PhpThumbFactory::create($upload_rep_fichier);
$thumb->resize(50,50)->save($upload_rep.'thumb_small/'.$nom_fichier);

// Medium
if(!file_exists($upload_rep.'/thumb_medium')){mkdir($upload_rep.'/thum
b_medium',0755);}
$thumb = PhpThumbFactory::create($upload_rep_fichier);
$thumb->resize(100,100)->save($upload_rep.'thumb_medium/'.$nom_fichier
);

// Large
if(!file_exists($upload_rep.'/thumb_large')){mkdir($upload_rep.'/thumb
_large',0755);}
$thumb = PhpThumbFactory::create($upload_rep_fichier);
$thumb->resize(150,150)->save($upload_rep.'thumb_large/'.$nom_fichier)
;
}
return true;
}
}
// On affiche les messages d'erreur Ã la vue
$this->erreurs($erreurs,$erreurs_models);
}
}

/*
* Supprime le fichier et ses miniatures
* @param string (nom du modÃ¨le)
* @param array (extension)
* @return -
**/
function delete($model,$conditions=null){

App::Import('Model',$model);
$Fichier = new $model();
$Fichier->recursive = 0;

if(!empty($conditions)){
$conditions = array('conditions'=>$conditions);
}

// Si on trouve le fichier
$fichier = $Fichier->find('first',$conditions);
if(!empty($fichier)){

// Supprime les fichiers physiques
// L'image principale
$f = new File($fichier[$model]['url_fichier']);
$f->delete();
// Les miniatures
$f = new File($fichier[$model]['url_rep'].'thumb_small/'.$fichier[$mod
el]['nom_fichier']);
$f->delete();
// Supprime le rep. thumb_small si vide
if($this->TailleRep($fichier[$model]['url_rep'].'thumb_small/')==0){
$f = new Folder($fichier[$model]['url_rep'].'thumb_small/');
$f->delete();
}
$f = new File($fichier[$model]['url_rep'].'thumb_medium/'.$fichier[$mo
del]['nom_fichier']);
$f->delete();
// Supprime le rep. thumb_medium si vide
if($this->TailleRep($fichier[$model]['url_rep'].'thumb_medium/')==0){
$f = new Folder($fichier[$model]['url_rep'].'thumb_medium/');
$f->delete();
}
$f = new File($fichier[$model]['url_rep'].'thumb_large/'.$fichier[$mod
el]['nom_fichier']);
$f->delete();
// Supprime le rep. thumb_large si vide
if($this->TailleRep($fichier[$model]['url_rep'].'thumb_large/')==0){
$f = new Folder($fichier[$model]['url_rep'].'thumb_large/');
$f->delete();
}

// Supprime le lien vers le fichier dans la table
$Fichier->delete($fichier[$model]['id']);

return true;
}else{
return false;
}
}

/*
* Lance un tÃ©lÃ©chargement
* @param string (nom du modÃ¨le qui stock les fichiers)
* @param array (conditions requises pour le fichier Ã downloader )
* @return bool
**/
function download($model,array $conditions=null)
{

App::Import('Model',$model);
$Fichier = new $model();
$Fichier->recursive = 0;

if(!empty($conditions)){
$conditions = array('conditions'=>$conditions);
}

// Si on trouve le fichier on lance le tÃ©lÃ©chargement
$fichier = $Fichier->find('first',$conditions);
if(!empty($fichier)){

$url_fichier = $fichier[$model]['url_fichier'];
$url_rep = $fichier[$model]['url_rep'];
$nom_fichier = $fichier[$model]['nom_fichier'];
$position = strpos($nom_fichier,'.');
$nom_fichier_s_ext = substr($nom_fichier, 0, $position);
$extension = strrchr($url_fichier,'.');

$this->controller->view = 'Media';

// Pour les "docx"
if($extension=='.docx'){
$extension = 'docx';
$params = array(
'id' => $nom_fichier,
'name' => $nom_fichier_s_ext,
'download' => true,
'extension' => 'docx',
'mimeType' => array('docx' => 'application/vnd.openxmlformats-
officedocument.wordprocessingml.document'),
'path' => APP.'webroot/'.$url_rep
);

// Pour tous les autres types de documents
}else{
$extension = substr($extension,1); // Pour avoir "zip" au lieu de
".zip"
$params = array(
'id' => $nom_fichier,
'name' => $nom_fichier_s_ext,
'download' => true,
'extension' => $extension,
'path' => APP.'webroot/'.$url_rep
);
}

$this->controller->set($params);
}
}

/*
* PrÃ©pare les erreurs Ã afficher dans la vue
* @param array (liste des erreurs d'upload)
* @param array (liste des erreurs du modÃ¨le)
* @return -
**/
function erreurs($erreurs,$erreurs_models){
$html = '';
if(!empty($erreurs_models) || !empty($erreurs)){$html.=' '.__('Les
erreurs suivantes ont Ã©tÃ© dÃ©tectÃ©es.',true).'
>';}<br > if(!empty($erreurs_models)){
foreach($erreurs_models as $erreur_model){
$html.= '
'.$erreur_model.'
';
}
}
if(!empty($erreurs)){
foreach($erreurs as $erreur){
$html.= '
'.$erreur.'
';
}
}
if(!empty($err_models) || !empty($erreurs)){ $html.='

