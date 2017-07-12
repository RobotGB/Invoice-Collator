<?PHP 

namespace Brainlabs\Invoicing;

use \PHPExcel_IOFactory;
use \Exception;

class InvoiceCollator 
{
    private $dir;

    public function __construct($dir) 
    {
        if (!is_dir($dir)) {
            throw new Exception("Specified directory does not exist: $dir");
        }

        $this->dir = $dir;
    }

    public function run()
    {
        for(array_diff(scandir($directory)) as $file) {
            $data = $this->collateFile($file);
        }
    }
    
    private function collateFile($file)
    {
        $ss = PHPExcel_IOFactory::load($file);
        print_r(get_class_methods($file));
    }
}
