
var mainApp = angular.module('mainApp',
    [
        'ngRoute',
        'ngResource',
        'ui.bootstrap',
        'ui.router',
        'ngDragDrop',
        'mainAppControllers',
        'userServices',
        'resourceControllers',
        'resourceServices',
        'modelControllers',
        'modelServices',
        'modelTryControllers',
        'modelTryServices',
        'itemControllers',
        'itemServices',
        'exerciseControllers',
        'exerciseServices',
        'answerServices',
        'exerciseByAttemptServices',
        'attemptServices'
    ]
);