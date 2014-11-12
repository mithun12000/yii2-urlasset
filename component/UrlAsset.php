<?php
namespace Yii\UrlAsset\component;
use Yii;
use yii\web\AssetBundle;
use yii\web\View as View;

/**
 * @author Mithun Mandal <mithun12000@gmail.com>
 */
class UrlAsset extends AssetBundle
{
	public $assetFile = 'AppUrlAsset';
    /**
     * @var array list of bundle class names that this bundle depends on.
     */
    public $depends = [];
    /**
     * @var array list of JavaScript files that this bundle contains. Each JavaScript file can
     * be either a file path (without leading slash) relative to [[basePath]] or a URL representing
     * an external JavaScript file.
     *
     * Note that only forward slash "/" can be used as directory separator.
     */
    public $url = [];
    /**
     * @var array list of CSS files that this bundle contains. Each CSS file can
     * be either a file path (without leading slash) relative to [[basePath]] or a URL representing
     * an external CSS file.
     *
     * Note that only forward slash "/" can be used as directory separator.
     */
    public $actionmap = [];
    
    
    public $module = '';
    
    public function init() {
        if(is_array($this->module)){            
            foreach($this->module as $module=>$urlmap){
                if ($module == 'site') continue;
                
                //\yii::trace('permission to check:'.$strmap);
                //if(\Yii::$app->user->can($strmap)){
                    if(isset($this->url[0][$urlmap]['items']) && count($this->url[0][$urlmap]['items'])){
                        $anymenu = false;
                        foreach($this->url[0][$urlmap]['items'] as $id=>$item){
                            //if(!isset($item['url'])) continue;
                            if(isset($item['permission'])){
                                if(!\Yii::$app->user->can($item['permission'])){
                                    unset($this->url[0][$urlmap]['items'][$id]);
                                }else{
                                    $anymenu = true;
                                }
                                unset($this->url[0][$urlmap]['items'][$id]['permission']);
                            }else{
                                $url = trim($item['url'][0], '/');
                                \yii::trace('permission to check:'.$url);
                                if(!\Yii::$app->user->can($url)){
                                    unset($this->url[0][$urlmap]['items'][$id]);
                                }else{
                                    $anymenu = true;
                                }
                            }
                        }
                        if(!$anymenu){
                            unset($this->url[0][$urlmap]);
                        }
                    }
                /*}else{
                    unset($this->url[0][$urlmap]);
                }//*/
            }
        }
    }
    
    /**
     * @param View $view
     * @return the registered asset bundle instance
     */
    public function registerAll($view)
    {        
        $this->checkDependency();
        return $view->registerAssetBundle(get_class($this));
    }
    
    /**
     * 
     * @param array $menuItems
     * @param array $menuArray
     * @return array Menu Array
     */
    private function MenuMerge($menuItems,$menuArray) {
        foreach($menuArray as $menuKey => $menu){
            if(isset($menuItems[$menuKey])){
                $menuItems[$menuKey]['items'] = array_merge_recursive($menuItems[$menuKey]['items'],$menu['items']);
            }else{
                $menuItems[$menuKey] = $menu;
            }
        }        
        return $menuItems;
    }

    /**
     * Registers url params on given View
     * @param \yii\web\View $view the view that the asset files are to be registered with.
     */
    public function setViewParams($view)
    {
        
        foreach ($this->url as $url) {
            if(isset($view->params['urls']) && is_array($view->params['urls'])){
                $view->params['urls'] = $this->MenuMerge($view->params['urls'],$url);
            }else{
                $view->params['urls'] = $url;
            }
            
        }
        
        if($module = $this->getModule()){
            $view->params['urls'][$module]['active']=true;
        }
    }
    
    public function setParams($view){
        $this->setViewParams($view);       
        $am = $view->getAssetManager();        
        // register dependencies        
        foreach ($this->depends as $dep) {
            $bundle = $am->getBundle($dep);            
            $bundle->setParams($view);
        }
        //defined hard code pull down trash menu to bottom
        if(isset($view->params['urls']['ztrash'])){
            $trash = $view->params['urls']['ztrash'];
            unset($view->params['urls']['ztrash']);
            //if(isset($trash['items']) && count($trash['items'])){
                $view->params['urls']['ztrash']=$trash;
                unset($trash);
            //}
        }
    }
    
    public function checkDependency() {
        $controllerNamespace = \Yii::$app->controllerNamespace; //base controller of App
        $modules = \Yii::$app->getModules(); //array of modules
        
        
        $this->depends = [];
        
        if($path = $this->getAssetPathNs($controllerNamespace)){            
            $this->depends[] = $path;
        }
        
        foreach($modules as $module){
            if($path = $this->getAssetPathNs($module)){                
                $this->depends[] = $path;
            }
        }
    }
    
    public function getAssetPathNs($path) {
        $strpath = '';
        if(is_array($path) && isset ($path['class'])){
            $strpath = $path['class'];
        }else if(is_object($path)){
            $strpath = get_class($path);
        }else{
            $strpath = $path;
        }
        
        if(is_string($strpath)){
            $_part = explode('\\',$strpath);
            array_pop($_part);
            $_part_asset = $_part_module = $_part;
            array_push($_part_module,$this->assetFile);
            array_push($_part_asset,'assets',$this->assetFile);            
            $module_path = implode('\\',$_part_module);
            $asset_path = implode('\\',$_part_asset);
            $_path = [$module_path,$asset_path];
            unset($_part_asset,$_part_module,$module_path,$asset_path);
            
            foreach($_path as $newpath){
                if(file_exists(Yii::getAlias('@'.str_replace('\\', '/', $newpath), false).'.php')){                    
                    return $newpath;
                }
            }
        }
        return false;
    }
    
    public function getModule() {
        if(!is_array($this->module)){
            return false;
        }
        
        if(array_key_exists(\Yii::$app->controller->module->id.'/'.\Yii::$app->controller->id,$this->module)){
            return $this->module[\Yii::$app->controller->module->id.'/'.\Yii::$app->controller->id];
        }
        
        if(array_key_exists(\Yii::$app->controller->module->id,$this->module)){
            return $this->module[\Yii::$app->controller->module->id];
        }
        return false;
    }
}