<?php

namespace App\Controller;

use App\Entity\Batch;
use App\Form\BatchType;
use App\Repository\BatchRepository;
use App\Repository\ModelRepository;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/batch")
 */
class BatchController extends AbstractController
{
    /**
     * @Route("/", name="batch_index", methods={"GET"})
     */
    public function index(BatchRepository $batchRepository): Response
    {
        
        return $this->render('batch/index.html.twig', [
            'batches' => $batchRepository->findAll(),
            'user'   => $this->getUser()
        ]);
    }

    /**
     * @Route("/new", name="batch_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $batch = new Batch();
        $form = $this->createForm(BatchType::class, $batch);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $batch->setUser($user);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($batch);
            $entityManager->flush();
            $this->generateFolderName($batch);
            $entityManager->flush();
            return $this->redirectToRoute('batch_edit',['id'=>$batch->getId()]);
        }

        return $this->render('batch/new.html.twig', [
            'batch' => $batch,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="batch_show", methods={"GET"})
     */
    public function show(Batch $batch): Response
    {
        return $this->render('batch/show.html.twig', [
            'batch' => $batch,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="batch_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Batch $batch): Response
    {
        $form = $this->createForm(BatchType::class, $batch);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->generateFolderName($batch);
            $this->getDoctrine()->getManager()->flush();
    
            return $this->redirectToRoute('batch_edit',['id'=>$batch->getId()]);
        }

        return $this->render('batch/edit.html.twig', [
            'batch' => $batch,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="batch_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Batch $batch): Response
    {
        if ($this->isCsrfTokenValid('delete'.$batch->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($batch);
            $entityManager->flush();
        }

        return $this->redirectToRoute('batch_index');
    }

    /**
     * @Route("/api/get-models-by-brand", name="batch_api_get_models_by_brand", methods={"GET"})
     */
    public function getModelsByBrand(Request $request,ModelRepository $model_repository): JsonResponse
    {
        $models = $model_repository->findByBrand($request->query->get('brand_id'));
        $responseArray = [];
        foreach($models as $model){
            $responseArray[] = [
                "id" => $model->getId(),
                "name" => $model->getName()
            ];
        }
        return new JsonResponse($responseArray);
    }

    /**
     * @Route("/api/get-services-by-category", name="batch_api_get_services_by_category", methods={"GET"})
     */
    public function getServicesByCategory(Request $request,ServiceRepository $service_repository): JsonResponse
    {
        $services = $service_repository->findByCategory($request->query->get('category_id'));
        $responseArray = [];
        foreach($services as $model){
            $responseArray[] = [
                "id" => $model->getId(),
                "name" => $model->getName()
            ];
        }
        return new JsonResponse($responseArray);
    }
    /**
     * @Route("/api/upload-images/{batch_id}", name="batch_api_upload-images", methods={"POST"})
     */
    public function uploadImages(Request $request,$batch_id): JsonResponse
    {
        $response = ['status'=>'OK','message'=>'Загружено'];
        return new JsonResponse($response);
    }
    
    /**
     * @param Batch $batch
     */
    private function generateFolderName($batch): void
    {
        $batch->setFolder($batch->getId() . '_' . $batch->getBrand()->getName() . '_' . $batch->getModel()->getName() . '_' . $batch->getServiceCategory()->getName() . '_' . $batch->getService()->getName());
    }
}
