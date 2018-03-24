<?php

namespace App\Controller;

use App\Document\ReportMail;
use App\Document\ReportSMS;
use Documents\CustomRepository\Repository;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

/**
 * Class ApiController
 *
 * @Route("/api")
 */
class ApiController extends FOSRestController
{
    // Report ADD

    /**
     * @Rest\Put("/v1/report.{_format}", name="report_add", defaults={"_format":"json"})
     *
     * @SWG\Response(
     *     response=201,
     *     description="Report was added successfully"
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="An error was occurred trying to add new report"
     * )
     *
     * @SWG\Parameter(
     *     name="type",
     *     in="body",
     *     type="string",
     *     description="The report type",
     *     schema={}
     * )
     *
     * @SWG\Parameter(
     *     name="reference",
     *     in="body",
     *     type="string",
     *     description="The report reference",
     *     schema={}
     * )
     *
     * @SWG\Parameter(
     *     name="status",
     *     in="body",
     *     type="string",
     *     description="The report status",
     *     schema={}
     * )
     *
     * @SWG\Parameter(
     *     name="timestamp",
     *     in="body",
     *     type="string",
     *     description="The report timestamp",
     *     schema={}
     * )
     *
     * @SWG\Parameter(
     *     name="network",
     *     in="body",
     *     type="string",
     *     description="The report network",
     *     schema={}
     * )
     *
     * @SWG\Parameter(
     *     name="client",
     *     in="body",
     *     type="string",
     *     description="The report client",
     *     schema={}
     * )
     *
     * @SWG\Parameter(
     *     name="os",
     *     in="body",
     *     type="string",
     *     description="The report os",
     *     schema={}
     * )
     * @SWG\Tag(name="Board")
     * @param Request $request
     * @return Response
     */
    public function addReportAction(Request $request)
    {
        $serializer = $this->get('jms_serializer');
        $message = "";
        $report = [];
        try {
            $code = 201;
            $error = false;
            $type = $request->get("type", null);

            switch ($type) {
                case 'SMS':
                    /** @var Repository $repository */
                    $repository = $this->get('doctrine_mongodb')
                        ->getManager()
                        ->getRepository('App:ReportSMS');
                    $timestamp = $request->request->get("timestamp", null);
                    $reference = $request->request->get("reference", null);
                    $status = $request->request->get("status", null);
                    $network = $request->request->get("network", null);
                    $report = $repository->findOneBy(array('reference' => $reference));
                    if ($report == null) {
                        $report = new ReportSMS();
                    }
                    $report->setNetwork($network);
                    $report->setReference($reference);
                    $report->setTimestamp($timestamp);
                    $report->setStatus($status);
                    $this->get('doctrine_mongodb')->getManager()->persist($report);
                    $this->get('doctrine_mongodb')->getManager()->flush();

                    break;
                case 'MAIL':
                    /** @var Repository $repository */
                    $repository = $this->get('doctrine_mongodb')
                        ->getManager()
                        ->getRepository('App:ReportMail');
                    $timestamp = $request->request->get("timestamp", null);
                    $reference = $request->request->get("reference", null);
                    $status = $request->request->get("status", null);
                    $client = $request->request->get("client", null);
                    $os = $request->request->get("os", null);
                    /** @var ReportMail $report */
                    $report = $repository->findOneBy(array('reference' => $reference));
                    if ($report == null) {
                        $report = new ReportSMS();
                    }
                    $report->setStatus($status);
                    $report->setReference($reference);
                    $report->setTimestamp($timestamp);
                    $report->setClient($client);
                    $report->setOs($os);
                    $this->get('doctrine_mongodb')->getManager()->persist($report);
                    $this->get('doctrine_mongodb')->getManager()->flush();
                    break;
                default:
                    $code = 500;
                    $error = true;
                    $message = "An error has occurred trying to add new report - Error: invalid type";
                    break;
            }

        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to add new report - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 201 ? $report : $message,
        ];

        return new Response($serializer->serialize($response, "json"));
    }


}
