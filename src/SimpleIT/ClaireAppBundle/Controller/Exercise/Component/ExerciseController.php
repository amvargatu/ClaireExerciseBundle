<?php

namespace SimpleIT\ClaireAppBundle\Controller\Exercise\Component;

use SimpleIT\ApiResourcesBundle\Exercise\Exercise\Common\CommonExercise;
use SimpleIT\ApiResourcesBundle\Exercise\ExerciseResource;
use SimpleIT\AppBundle\Controller\AppController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ExerciseController
 *
 * @author Baptiste Cablé <baptiste.cable@liris.cnrs.fr>
 */
class ExerciseController extends AppController
{
    /**
     * View an exercise item. The answer form is shown if the exercise has not yet been answered.
     * If it has been answered, the answer and the correction are shown.
     *
     * @param int $exerciseId
     * @param int $itemNumber
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction($exerciseId, $itemNumber = 1)
    {
        $corrected = false;

        $item = $this->get('simple_it.claire.exercise.item')->getItemObjectFromExerciseAndItem(
            $exerciseId,
            $itemNumber,
            $corrected
        );

        $exercise = $this->get('simple_it.claire.exercise.exercise')->getExerciseObjectFromExercise(
            $exerciseId
        );

        if ($corrected === true) {
            $view = $this->selectCorrectedView($exercise);
        } else {
            $view = $this->selectNotCorrectedView($exercise);
        }


        return $this->render(
            $view,
            array(
                'exercise'   => $exercise,
                'item'       => $item,
                'itemNumber' => $itemNumber,
                'exerciseId' => $exerciseId
            )
        );
    }

    /**
     * Answer action. Post the learner's answer
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function answerAction(Request $request)
    {
        $exerciseId = $request->get('exerciseId');
        $itemNumber = $request->get('itemNumber');
        $answers = $request->get('answers');

        // TODO récupérer l'item pour le passer à add

        $this->get('simple_it.claire.exercise.answer')->add(
            $exerciseId,
            $itemNumber,
            $answers
        );

        return $this->redirect(
            $this->generateUrl(
                'simple_it_exercise_exercise_view',
                array(
                    'exerciseId' => $exerciseId,
                    'itemNumber' => $itemNumber
                )
            )
        );
    }

    /**
     * Get the corrected view corresponding to the type of exercise.
     *
     * @param CommonExercise $exercise
     *
     * @return string
     * @throws \LogicException
     */
    private
    function selectCorrectedView(
        $exercise
    )
    {
        switch (get_class($exercise)) {
            case ExerciseResource::MULTIPLE_CHOICE_CLASS:
                $view = 'SimpleITClaireAppBundle:Exercise/MultipleChoice/Component:corrected.html.twig';
                break;
            case ExerciseResource::GROUP_ITEMS_CLASS:
                $view = 'SimpleITClaireAppBundle:Exercise/GroupItems/Component:corrected.html.twig';
                break;
            case ExerciseResource::ORDER_ITEMS_CLASS:
                $view = 'SimpleITClaireAppBundle:Exercise/OrderItems/Component:corrected.html.twig';
                break;
            case ExerciseResource::PAIR_ITEMS_CLASS:
                $view = 'SimpleITClaireAppBundle:Exercise/PairItems/Component:corrected.html.twig';
                break;
            default:
                throw new \LogicException('Unknown class of exercise: ' . get_class($exercise));
        }

        return $view;
    }

    /**
     * Get the not corrected view corresponding to the type of exercise.
     *
     * @param CommonExercise $exercise
     *
     * @return string
     * @throws \LogicException
     */
    private
    function selectNotCorrectedView(
        $exercise
    )
    {
        switch (get_class($exercise)) {
            case ExerciseResource::MULTIPLE_CHOICE_CLASS:
                $view = 'SimpleITClaireAppBundle:Exercise/MultipleChoice/Component:answerForm.html.twig';
                break;
            case ExerciseResource::GROUP_ITEMS_CLASS:
                $view = 'SimpleITClaireAppBundle:Exercise/GroupItems/Component:answerForm.html.twig';
                break;
            case ExerciseResource::ORDER_ITEMS_CLASS:
                $view = 'SimpleITClaireAppBundle:Exercise/OrderItems/Component:answerForm.html.twig';
                break;
            case ExerciseResource::PAIR_ITEMS_CLASS:
                $view = 'SimpleITClaireAppBundle:Exercise/PairItems/Component:answerForm.html.twig';
                break;
            default:
                throw new \LogicException('Unknown class of exercise: ' . get_class($exercise));
        }

        return $view;
    }
}
