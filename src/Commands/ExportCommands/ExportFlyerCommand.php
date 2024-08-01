<?php


namespace CTExport\Commands\ExportCommands;

use CTApi\Requests\EventRequest;
use CTExport\Commands\Traits\LoadGroups;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'export:flyer',
    description: 'Export flyer for given events.',
    hidden: false,
)]
class ExportFlyerCommand extends ExportCommand
{
    use LoadGroups;

    const EVENT_ID = "event_id";
    const TEMPLATE_NAME = "template_name";

    protected function configure()
    {
        parent::configure();
        $this->addArgument(self::TEMPLATE_NAME, InputArgument::REQUIRED, "Word Template.");
        $this->addArgument(self::EVENT_ID, InputArgument::REQUIRED, "Event id to create flyer from.");
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $eventId = (int)$input->getArgument(self::EVENT_ID);
        $templateName = $input->getArgument(self::TEMPLATE_NAME);

        $output->writeln("Load Template file: " . $templateName);

        $event = EventRequest::findOrFail($eventId);
        $output->writeln("Loaded Event: " . $event->getName() . '(#' . $event->getId() .')');

        $templateProcessor = new TemplateProcessor($templateName);

        foreach($event->toData() as $key => $value){
            if(is_string($value)){
                $templateProcessor->setValue($key, $value);
                $output->writeln("\ttry to replace " . $key . " with value: " . $value);
            }
        }

        $filePath = $this->createWordPath("flyer-export");
        $templateProcessor->saveAs($filePath);
        $output->writeln("Exported flyer in word to " . $filePath);

        /**
         * PDF - Not working at the moment.
         */
        /*
        WITH MPDF:ini_set("pcre.backtrack_limit", "5000000");
        \PhpOffice\PhpWord\Settings::setPdfRendererName(\PhpOffice\PhpWord\Settings::PDF_RENDERER_MPDF);
        \PhpOffice\PhpWord\Settings::setPdfRendererPath(__DIR__ . '/../../../vendor/mpdf/mpdf');
        */
        \PhpOffice\PhpWord\Settings::setPdfRendererName(\PhpOffice\PhpWord\Settings::PDF_RENDERER_DOMPDF);
        \PhpOffice\PhpWord\Settings::setPdfRendererPath(__DIR__ . '/../../../vendor/mpdf/mpdf');

        $reader = IOFactory::load($filePath);
        $writer = IOFactory::createWriter($reader, "PDF");
        $filePDFPath = $this->createPdfPath("flyer-export");
        $writer->save($filePDFPath);
        $output->writeln("Exported flyer in pdf to " . $filePDFPath);

        return 1;
    }
}