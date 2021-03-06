<?php

namespace App\Controller;

use App\Entity\Batch;
use App\Entity\Photo;
use App\Form\BatchFilterType;
use App\Form\BatchType;
use App\Repository\BatchRepository;
use App\Repository\ModelRepository;
use App\Repository\ServiceRepository;
use App\Repository\TypeRepository;
use App\Services\YandexDiskService;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
     * @var YandexDiskService
     */
    protected $disk_service;
    
    public function __construct(YandexDiskService $disk_service)
    {
        $this->disk_service = $disk_service;
    }
    
    /**
     * @Route("/", name="batch_index", methods={"GET"})
     */
    public function index(BatchRepository $batchRepository,PaginatorInterface $paginator,Request $request,ParameterBagInterface $params): Response
    {
        $batch = new Batch();
        $formFilter = $this->createForm(BatchFilterType::class, $batch,[
            'method' => 'GET',
            'required' => false,
        ]);
        $formFilter->handleRequest($request);
        $filterData = $formFilter->getData();
        $using = $formFilter['using']->getData();
        $year_month = $formFilter['year_month']->getData();
        
        $pagination = $paginator->paginate(
            $batchRepository->getAllFilteredQB($filterData,$using,$year_month,$request), /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            100/*limit per page*/
        );
        $total_photos = 0;
        /** @var Batch $item */
        foreach ($pagination as $item) {
            $total_photos+=$item->getPhotos()->count();
        }
    
        $yandex           = $params->get('yandex');
        $root_dir = '/'.$yandex['root_dir'].'/';
        $root_dir = preg_replace('#\/+#','/',$root_dir);
        
        return $this->render('batch/index.html.twig', [
            'user'    => $this->getUser(),
            'pagination' => $pagination,
            'total_photos' => $total_photos,
            'root_dir' => $root_dir,
            'formFilter' => $formFilter->createView(),
        ]);
    }
    
    /**
     * @Route("/new", name="batch_new", methods={"GET","POST"})
     * @IsGranted("ROLE_EDITOR_ADD")
     */
    public function new(Request $request): Response
    {
        $batch = new Batch();
        $form  = $this->createForm(BatchType::class, $batch);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $batch->setUser($user);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($batch);
            $entityManager->flush();
            $folder = $this->generateFolderName($batch);
            $batch->setFolder($folder);
            try{
                $this->disk_service->createDirectory($folder);
            } catch (\Exception $e){
                $this->addFlash('danger',"Ошибка при создании папки: ".$e->getMessage());
            }
            $link = $this->disk_service->publishFolder($batch->getFolder());
            $batch->setLink($link);
            $entityManager->flush();
            
            return $this->redirectToRoute('batch_edit', ['id' => $batch->getId()]);
        }
        
        return $this->render('batch/new.html.twig', [
            'batch' => $batch,
            'form'  => $form->createView(),
        ]);
    }
    
    /**
     * @Route("/publish_folders", name="publish_folders", methods={"GET"})
     */
    public function publish_folders(BatchRepository $batchRepository): Response
    {
        $em = $this->getDoctrine()->getManager();
        $batches = $batchRepository->findAll();
        foreach ($batches as $batch) {
            $link = $this->disk_service->publishFolder($batch->getFolder());
            $batch->setLink($link);
        }
        $em->flush();
        return new Response('Ok');
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
        try{
            $files = $this->disk_service->getFilesOfFolder($batch->getFolder());
        } catch (\Exception $e){
            $this->addFlash('danger',"Ошибка при получении списка изображений из папки ".$batch->getFolder().": ".$e->getMessage());
            $files=[];
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            //Сохраняем домен
            $em->flush();
            $new_folder = $this->generateFolderName($batch);
            $old_folder = $batch->getFolder();
            if ($new_folder !== $old_folder) {
                try{
                    $this->disk_service->move($old_folder.'/', $new_folder.'/');
                    $batch->setFolder($new_folder);
                    $em->flush();
                    
                } catch (\Exception $e){
                    $this->addFlash('danger',"Ошибка при перемещении файлов: ".$e->getMessage());
                }
            }
            
            
            return $this->redirectToRoute('batch_edit', ['id' => $batch->getId()]);
        }
        
        return $this->render('batch/edit.html.twig', [
            'batch' => $batch,
            'form'  => $form->createView(),
            'files' => $files
        ]);
    }
    
    /**
     * @Route("/{id}", name="batch_delete", methods={"DELETE"})
     * @IsGranted("ROLE_EDITOR_DELETE")
     */
    public function delete(Request $request, Batch $batch): Response
    {
        if ($this->isCsrfTokenValid('delete' . $batch->getId(), $request->request->get('_token'))) {
            /*if (!$this->disk_service->delete($batch->getFolder().'/')) {
                $this->addFlash('danger','Не удалось удалить папку на диске');
                return $this->redirectToRoute('batch_edit', ['id' => $batch->getId()]);
            }*/
            $entityManager = $this->getDoctrine()->getManager();
            foreach ($batch->getPhotos() as $photo){
                //$this->disk_service->delete($batch->getFolder().'/'.$photo->getPath());
                $entityManager->remove($photo);
            }
            $entityManager->remove($batch);
            $entityManager->flush();
        }
        
        return $this->redirectToRoute('batch_index');
    }
    
    /**
     * @Route("/api/get-models-by-brand", name="batch_api_get_models_by_brand", methods={"GET"})
     */
    public function getModelsByBrand(Request $request, ModelRepository $model_repository): JsonResponse
    {
        $models        = $model_repository->findByBrand($request->query->get('brand_id'));
        $responseArray = [];
        foreach ($models as $model) {
            $responseArray[] = [
                "id"   => $model->getId(),
                "name" => $model->getName(),
            ];
        }
        
        return new JsonResponse($responseArray);
    }
    
    /**
     * @Route("/api/get-services-by-category", name="batch_api_get_services_by_category", methods={"GET"})
     */
    public function getServicesByCategory(Request $request, ServiceRepository $service_repository): JsonResponse
    {
        $services      = $service_repository->findByCategory($request->query->get('category_id'));
        $responseArray = [];
        foreach ($services as $model) {
            $responseArray[] = [
                "id"   => $model->getId(),
                "name" => $model->getName(),
            ];
        }
        
        return new JsonResponse($responseArray);
    }
    
    /**
     * @Route("/api/upload-images/{id}", name="batch_api_upload-images", methods={"POST"})
     * @IsGranted("ROLE_EDITOR_ADD")
     */
    public function uploadImages(Request $request,YandexDiskService $disk_service, Batch $batch): JsonResponse
    {
        /** @var UploadedFile */
        $image_file = $request->files->get('file');
        $path_to_file = $image_file->getRealPath();
        $file_name = $image_file->getClientOriginalName();
        try{
            if ($disk_service->uploadFile($batch->getFolder(), $file_name, $path_to_file)) {
                $response = ['status' => 'OK', 'message' => 'Загружено'];
                $photo = (new Photo())->setPath($file_name)->setBatch($batch);
                $em = $this->getDoctrine()->getManager();
                $em->persist($photo);
                $em->flush();
            }
            else{
                $response = ['status' => 'ERROR', 'message' => 'Ошибка при передаче на Я.Диск'];
            }
        } catch (\Exception $e){
            $response = ['status' => 'ERROR', 'message' => $e->getMessage()];
        }
        
        return new JsonResponse($response);
    }
    
    /**
     * @param Batch $batch
     *
     * @return string
     */
    private function generateFolderName($batch): string
    {
        $folder_name = $batch->getId() . '_' . $batch->getBrand()->getName() . '_' . $batch->getModel()->getName() . '_' . $batch->getServiceCategory()->getName() . '_' . $batch->getService()->getName();
        if ($batch->getName()) {
            $folder_name.= ' ('.$batch->getName().')';
        }
        return $folder_name;
        
    }
    
}
