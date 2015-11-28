A Yii2 extension for handling multistep forms.
==============================================
A Yii2 extension for easy handling multistep forms.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist dergus/yii2-whitewizard "*"
```

or add

```
"dergus/yii2-whitewizard": "*"
```

to the require section of your `composer.json` file.


Usage
-----

There are two ways of using this action:
1)Every step is just normal yii action.
2)All steps in action and differtiated by the http varible

==============================================

How to use extension in 1-st way:

first you need to add extension in 
controllers behaviors with configuration:
<?php
namespace app\controllers;

use Yii;
use app\models\First;
use app\models\Second;
use app\models\Third;
use dergus\whitewizard\WhiteWizard;

class SiteController extends \yii\web\Controller
{
    public function behaviors()
    {
	    /**
	     * controller actions which
	     * are available only in
	     * step chain
	     * @var array
	     */
        $closedActions=['confirmation'];
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
        $steps[]='first';
        $steps[]='second'

        return [

            'WhiteWizard'=>[
                'class'=>WhiteWizard::className(),
                '_steps'=>$steps,
                '_closedActions'=>$closedActions,
                /**
			     * step after which
			     * user can't go backward
			     * @var string
			     */
                '_noBackStep'=>'second'

            ],

        ];
    }

    Then in actions:

    public function actionFirst()
    {

		
        $model=new First;

        //must do to show user his data
        //if he went back

        if(($s=Yii::$app->session->get('first'))!==null){
            $model=$s;
        }
        if ($model->load(Yii::$app->request->post())) {
            if($model->validate()){
                $this->handleStep();//must call if the step is successfull
                Yii::$app->session->set('first',$model);//must do to save data from this step for further saving
                $next='second';
                return $this->redirect([$next]);}
            else{
            	//must call if data is not valid
                $this->notHandled();
            }
        } 

		return $this->render('first',compact('model'));
    }

All th same:

    public function actionSecond()
    {

		
        $model=new Second;

        //must do to show user his data
        //if he went back

        if(($s=Yii::$app->session->get('second'))!==null){
            $model=$s;
        }
        if ($model->load(Yii::$app->request->post())) {
            if($model->validate()){
                $this->handleStep();//must call if the step is successfull
                Yii::$app->session->set('second',$model);//must do to save data from this step for further saving
                $next='third';
                return $this->redirect([$next]);}
            else{
            	//must call if data is not valid
                $this->notHandled();
            }
        } 

		return $this->render('third',compact('model'));
    }

And the last step shuld be a little bit different:

    public function actionThird()
    {

		
        $model=new Third;

        //must do to show user his data
        //if he went back

        if(($s=Yii::$app->session->get('third'))!==null){
            $model=$s;
        }
        if ($model->load(Yii::$app->request->post())) {

        	//the implemenatation of $model->saveData() method
        	//is up ti you but it should save data from all the steps.
        	//Data from previous steps can be accessed from session
			//by key you save it, for example:
			//Yii::$app->session->get('first')

            if($model->validate() && $model->saveData()){
                
                //claen the session

                Yii::$app->session->set('first');
                Yii::$app->session->set('second');
				Yii::$app->session->set('third');
				$this->allDone();
                $next='success';
                return $this->redirect([$next]);}

        } 

		return $this->render('success',compact('model'));
    }

}

The usage for 2-nd way(not recommended cause makes code messy):

---- will be added soon ----

