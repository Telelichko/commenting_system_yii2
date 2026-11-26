public function behaviors()
{
    return [
        'verbs' => [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => ['delete' => ['POST']],
        ],
        'access' => [
            'class' => \yii\filters\AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['admin'],
                ],
            ],
        ],
    ];
}