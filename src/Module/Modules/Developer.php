<?php
namespace Bot\Module\Modules;

use Bot\Configure\Configure;
use Bot\Message\Message;
use Bot\Module\ModuleInterface;
use Bot\Network\Http\Client;
use Bot\Network\Wrapper;
use Bot\Utility\Command;
use Bot\Utility\User;

class Developer implements ModuleInterface
{
    /**
     * {@inheritDoc}
     *
     * @param \Bot\Network\Wrapper $wrapper The Wrapper instance.
     * @param array $message The message array.
     *
     * @return void
     */
    public function onChannelMessage(Wrapper $wrapper, $message)
    {
    }

    /**
     * {@inheritDoc}
     *
     * @param \Bot\Network\Wrapper $wrapper The Wrapper instance.
     * @param array $message The message array.
     *
     * @return void
     */
    public function onPrivateMessage(Wrapper $wrapper, $message)
    {
    }

    /**
     * {@inheritDoc}
     *
     * @param \Bot\Network\Wrapper $wrapper The Wrapper instance.
     * @param array $message The message array.
     *
     * @return bool
     */
    public function onCommandMessage(Wrapper $wrapper, $message)
    {
        //Handle the command.
        switch ($message['command']) {
            case 'dev':
                switch ($message['arguments'][0]) {
                    case 'info':
                        switch ($message['arguments'][1]) {
                            case 'memory':
                                $memoryKo = round(memory_get_usage() / 1024);
                                $memoryMo = number_format($memoryKo / 1024, 1, ',', ' ');

                                $wrapper->Channel->sendMessage('Memory used : `' . $memoryKo . 'Ko` (`' . $memoryMo . 'Mo`).');
                                break;

                            case 'server':
                                //Work only on Windows and Linux.
                                $serverInfo = $this->_getServerInfo();

                                $wrapper->Channel->sendMessage($serverInfo);
                                break;

                            case 'files':
                                $files = count(get_included_files());

                                $wrapper->Channel->sendMessage('There\'s `' . $files . '` files loaded in memory.');
                                break;

                            default:
                                $wrapper->Channel->sendMessage(Command::unknown($message));
                        }
                        break;

                    default:
                        $wrapper->Channel->sendMessage(Command::unknown($message));
                }
                break;
        }
    }

    /**
     * Get information about the sserver.
     *
     * @return string
     */
    protected function _getServerInfo()
    {
        if (stristr(PHP_OS, 'win')) {
            $wmi = new \COM("Winmgmts://");
            $processor = $wmi->execquery("SELECT Name FROM Win32_Processor");
            $physicalMemory = $wmi->execquery("SELECT Capacity FROM Win32_PhysicalMemory");
            $baseBoard = $wmi->execquery("SELECT * FROM Win32_BaseBoard");
            $threads = $wmi->execquery("SELECT * FROM Win32_Process");
            $disks = $wmi->execquery("SELECT * FROM Win32_DiskQuota");

            foreach ($processor as $wmiProcessor) {
                $name = $wmiProcessor->Name;
            }

            $memory = 0;
            foreach ($physicalMemory as $wmiPhysicalMemory) {
                $memory += $wmiPhysicalMemory->Capacity;
            }

            $memoryMo = ($memory / 1024 / 1024);
            $memoryGo = $memoryMo / 1024;

            foreach ($baseBoard as $wmiBaseBoard) {
                $boardName = $wmiBaseBoard->Product;
                $boardName .= ' ' . $wmiBaseBoard->Manufacturer;
            }

            $phrase = 'Server Information : ';

            $phrase .= '
Processor : ' . $name;

            $phrase .= '
Memory : ' . round($memoryMo, 2) . 'Mo (' . round($memoryGo, 2) . 'Go)';

            $phrase .= '
MotherBoard : ' . $boardName;

            $phrase .= '
Threads Information :';

            $threadsCount = 0;
            $totalMemoryUsed = 0;

            foreach ($threads as $thread) {
                $phrase .= '
    Name : ' . $thread->Name;

                $phrase .= '
    Threads Count : ' . $thread->ThreadCount;

                $totalMemoryUsed += ($thread->WorkingSetSize / 1024 / 1024);
                $memoryKo = ($thread->WorkingSetSize / 1024);
                $memoryMo = $memoryKo / 1024;

                $phrase .= '
    Memory used : ' . round($memoryKo, 2) . 'Ko (' . round($memoryMo, 2) . 'Mo)';

                $ngProcessTime = ($thread->KernelModeTime + $thread->UserModeTime) / 10000000;

                $phrase .= '
    Processor used by the process : ' . round($ngProcessTime, 2);

                $phrase .= '
    ProcessID : ' . $thread->ProcessID . '

';
                $threadsCount += $thread->ThreadCount;
            }

            $phrase .= '
Total Memory Used : ' . round($totalMemoryUsed, 2) . 'Mo' . '(' . round($totalMemoryUsed / 1024, 2) . 'Go)';

            $phrase .= '
Total Threads Count : ' . $threadsCount . ' threads';

            /*$http = new Http();
            $response = $http->post('http://pastebin.com/api/api_post.php', [
                'api_option' => 'paste',
                'api_dev_key' => Configure::read('Pastebin.apiDevKey'),
                'api_user_key' => '',
                'api_paste_private' => Configure::read('Pastebin.apiPastePrivate'),
                'api_paste_expire_date' => Configure::read('Pastebin.apiPasteExpireDate'),
                'api_paste_code' => $phrase
            ]);

            if (substr($response->getBody(), 0, 15) === 'Bad API request') {
                return 'Erreur to post the paste on Pastebin. Error : ' . $response->body;
            }

            $phrase = 'Server info : ' . $response->body;*/

            return $phrase;
        } elseif (PHP_OS == 'Linux') {
            $version = explode('.', PHP_VERSION);
            $phrase = 'PHP Version : `' . $version[0] . '.' . $version[1] . '`' . PHP_EOL;


            // File that has it
            $file = '/proc/cpuinfo';
            // Not there?
            if (!is_file($file) || !is_readable($file)) {
                return 'Unknown';
            }

            // Get contents
            $contents = trim(file_get_contents($file));

            // Lines
            $lines = explode("\n", $contents);

            // Holder for current CPU info
            $cpu = [];

            // Go through lines in file
            $numLines = count($lines);

            for ($i = 0; $i < $numLines; $i++) {
                $line = explode(':', $lines[$i], 2);

                if (!array_key_exists(1, $line)) {
                    continue;
                }

                $key = trim($line[0]);
                $value = trim($line[1]);

                // What we want are MHZ, Vendor, and Model.
                switch ($key) {
                    // CPU model
                    case 'model name':
                    case 'cpu':
                    case 'Processor':
                        $cpu['Model'] = $value;
                        break;
                    // Speed in MHz
                    case 'cpu MHz':
                        $cpu['MHz'] = $value;
                        break;
                    case 'Cpu0ClkTck': // Old sun boxes
                        $cpu['MHz'] = hexdec($value) / 1000000;
                        break;
                    // Brand/vendor
                    case 'vendor_id':
                        $cpu['Vendor'] = $value;
                        break;
                    // CPU Cores
                    case 'cpu cores':
                        $cpu['Cores'] = $value;
                        break;
                }
            }

            $phrase .= "Processor : `" . $cpu['Model'] . '`';

            return $phrase;
        } else {
            return 'This function work only on a Windows system. :(';
        }
    }
}
