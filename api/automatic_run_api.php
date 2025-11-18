<?php
class automatic_run_api
{
    private string $pythonPath;
    private string $apiScript;
    private int $port;
    private string $projectDir;

    public function __construct(
        string $pythonPath = 'C:\\Users\\csdmi\\AppData\\Local\\Programs\\Python\\Python313\\python.exe',
        string $apiScript = 'C:\\xampp\\htdocs\\LINYA\\faceapp_php\\main.py',
        int $port = 5000
    ) {
        $this->pythonPath = $pythonPath;
        $this->apiScript = $apiScript;
        $this->port = $port;
        $this->projectDir = dirname($apiScript);
    }

    private function isRunning(): bool
    {
        // Try to make an actual request to the API's health check endpoint
        $ch = curl_init("http://127.0.0.1:" . $this->port . "/model_status");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Any response (even 404) means the server is running
        return $httpCode !== 0;
    }

    public function start(): void
    {
        if (!$this->isRunning()) {
            $logFile = $this->projectDir . '\\face_api_log.txt';

            // Kill any existing Python processes
            exec("taskkill /F /IM python.exe 2>nul");
            sleep(2); // Wait for processes to be killed

            // Run Flask API in the correct directory, logging output
            $cmd = "cd /d {$this->projectDir} && start /B {$this->pythonPath} {$this->apiScript} > {$logFile} 2>&1";
            pclose(popen($cmd, "r"));

            // Wait up to 30 seconds for Flask to start
            $timeout = 30;
            $elapsed = 0;
            while ($elapsed < $timeout) {
                if ($this->isRunning()) {
                    sleep(3); // give extra time to stabilize
                    return;
                }
                sleep(1);
                $elapsed++;
            }

            // ⚠️ Only show this if it failed completely
            echo "<p style='color:red;'>⚠️ Flask API failed to start within {$timeout} seconds. Check face_api_log.txt for details.</p>";
        }
    }
}
?>