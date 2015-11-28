<?php
namespace dergus\whitewizard;

use yii\base\Behavior;
use yii\web\Controller;
use Yii;

class WhiteWizard extends Behavior
{
    /**
     * array of steps in order
     * in which the wizard
     * will process them
     * can be in two formats:
     * 1-st, when steps are
     * action ids:
     * ['first','second','third'].
     * 2-nd, when all the steps in on action
     * and they're distinguished by query
     * param, it shuold associative
     * array where the keys are action ids
     * and the values are arrays with
     * step names:
     * ['index'=>[
     *     'first',
     *     'second'
     * ],'contact'=>[
     *    'first',
     *    'second' 
     * ]]
     * 
     * 
     * @var array
     */
    
    public $_steps;

    /**
    * session key for the session  vars
    * of the wizard, will be added in 
    * the beginning of their names
    * @var string
    */
   
    public $_sessionKey='WhiteWizard';

    /**
     * step to which user
     * will be redirected if the current step
     * is not valid
     * @var string
     */
    
    public $_invalidStep='invalid-step';

    /**
     * step after which
     * user can't go backward
     * @var string
     */
    
    public $_noBackStep;
    /**
     * controller actions which
     * are available only in
     * step chain
     * @var array
     */
    
    public $_closedActions;

    /**
     * whether the steps
     * represented as
     * different actions
     * or located in one
     * @var boolean
     */
    
    public $_oneAction=false;
    /**
     * http method
     * in which you will send
     * step name.
     * only when $_oneAction=true
     * @var [type]
     */
    
    public $_stepMethod;

    /**
     * name of the http
     * var in which to find
     * step name.
     * only when $_oneAction=true
     * @var string
     */
    
    public $_stepKey='step';

    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => '_checkStep',
        ];
    }


    public function setStepMethod($method=null)
    {
        if($method==null) $this->_stepMethod=$_GET;
        else $this->_stepMethod=$method;
    }

/**
 * checks if the requested
 * step is valid
 * 
 */
    public function _checkStep()
    {
        $currentAction=$this->owner->action->id;

        if(!$this->_oneAction){ 
            $step=$currentAction;
            $steps=$this->_steps;
        }
        elseif(isset($this->_steps[$currentAction])){

                $steps=$this->_steps[$currentAction];

                if(isset($this->_stepMethod[$this->_stepKey]) ) {
                    $step=$this->_stepMethod[$this->_stepKey];
                    if(!isset($steps[$step])) $step=$steps[0];
                }

                else $step=$steps[0];
        }

        $previousStep=Yii::$app->session->get($this->_sessionKey.'.previousSteps',$step);
        
        if(($index=array_search($step,$steps))!==false){

                    $slice=array_slice($steps, 0, $index);
                    $diff=array_diff($slice,$this->_getHandledSteps());
                    if(!empty($diff)){
                        $this->owner->redirect([$this->owner->id.'/'.$this->_invalidStep]);
                    }

                    $previous=array_search($previousStep,$steps);
                    if(isset($this->_noBackStep) && ($this->getUpperStep()>=$this->_noBackStep)){
                        if(($index<$previous)){
                            
                                 $this->owner->redirect([$this->owner->id.'/'.$this->_invalidStep]);
                            
                        }
                    }
        
        Yii::$app->session->set($this->_sessionKey.'.previousSteps',$step);

        }elseif((array_search($step,$this->_closedActions))!==false){
                $this->owner->redirect([$this->owner->id.'/'.$this->_invalidStep]);
        }
    


    }

    public function _getHandledSteps()
    {
        return $a=($h=Yii::$app->session->get($this->_sessionKey.'.handledSteps'))==null?[]:$h;
    }
/**
 * adds step into list of
 * handled steps
 */
    public function handleStep()
    {
        $step=$this->owner->action->id;
        if((array_search($step,$this->_getHandledSteps()))===false){
            $handledSteps=($h=$this->_getHandledSteps())==null?[]:$h;
            array_push($handledSteps,$step);
            Yii::$app->session->set($this->_sessionKey.'.handledSteps',$handledSteps);
        }
    }
/**
 * removes step
 * from list of handled
 * steps
 */
    public function notHandled()
    {
        $step=$this->owner->action->id;
        if(($index=array_search($step,$this->_getHandledSteps()))!==false){
            $handledSteps=($h=$this->_getHandledSteps())==null?[]:$h;
            array_splice($handledSteps,$index,1);
            Yii::$app->session->set($this->_sessionKey.'.handledSteps',$handledSteps);
        }
    }
/**
 * removes wizard session
 * variables.
 * Call when all steps done.
 */
    public function allDone()
    {
        Yii::$app->session->remove($this->_sessionKey.'.handledSteps');
        Yii::$app->session->remove($this->_sessionKey.'.previousSteps');
    }


    public function getUpperStep()
    {
        if(!$this->_oneAction){
            $steps=$this->_steps[$this->owner->action->id];
        }else{
            $steps=$this->_steps;  
        }

        $hs=$this->_getHandledSteps();
        $index=0;
        foreach ($hs as $value) {
            $i=array_search($value, $this->_steps);
            if($i>$index) $index=$i;
        }

        return $index;
    }




}