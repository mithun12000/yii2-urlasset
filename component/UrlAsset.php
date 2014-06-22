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
     * Registers url params on given View
     * @param \yii\web\View $view the view that the asset files are to be registered with.
     */
    public function setViewParams($view)
    {
        if($this->module){
            if(is_array($this->module)){
                if(in_array(\Yii::$app->controller->module->id,$this->module)){
                    $this->url[0][0]['active']=true;
                }
            }else{
                if(\Yii::$app->controller->module->id == $this->module){
                    $this->url[0][0]['active']=true;
                }
            }
        }
        
        foreach ($this->url as $url) {
            if(is_array($view->params['urls'])){
                $view->params['urls'] = array_merge($view->params['urls'],$url);
            }else{
                $view->params['urls'] = $url;
            }
            
        }
        foreach ($this->actionmap as $actionmap) {
            if(is_array($view->params['actionmap'])){
                $view->params['actionmap'] = array_merge($view->params['actionmap'],$actionmap);
            }else{
                $view->params['actionmap'] = $actionmap;
            }
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
}