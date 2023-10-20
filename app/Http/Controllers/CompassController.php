<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CompassController extends Controller
{
  use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


  // @todo: 아이콘 리스트페이지, 커맨드 실행 페이지, 로그 확인페이지
  public function getLogs(){
    $logs = LogViewer::all();
    return json_encode($logs);
  }
  public function getCommands(){
    $commands = $this->getArtisanCommands();
    return json_encode($commands);
  }

  public function runCommand(Request $request){
    $command = $request->input('command');
    try {
      Artisan::call($command);
      $artisan_output = Artisan::output();
    } catch (Exception $e) {
      $artisan_output = $e->getMessage();
    }
    return $artisan_output;
  }

  private function getArtisanCommands()
  {
    Artisan::call('list');

    // Get the output from the previous command
    $artisan_output = Artisan::output();
    $artisan_output = $this->cleanArtisanOutput($artisan_output);
    $commands = $this->getCommandsFromOutput($artisan_output);

    return $commands;
  }

  private function cleanArtisanOutput($output)
  {

    // Add each new line to an array item and strip out any empty items
    $output = array_filter(explode("\n", $output));

    // Get the current index of: "Available commands:"
    $index = array_search('Available commands:', $output);

    // Remove all commands that precede "Available commands:", and remove that
    // Element itself -1 for offset zero and -1 for the previous index (equals -2)
    $output = array_slice($output, $index - 2, count($output));

    return $output;
  }

  private function getCommandsFromOutput($output)
  {
    $commands = [];

    foreach ($output as $output_line) {
      if (empty(trim(substr($output_line, 0, 2)))) {
        $parts = preg_split('/  +/', trim($output_line));
        $command = (object) ['name' => trim(@$parts[0]), 'description' => trim(@$parts[1])];
        array_push($commands, $command);
      }
    }
    return $commands;
  }
}

class LogViewer
{
  /**
   * @var string file
   */
  private static $file;

  private static $levels_classes = [
    'debug'     => 'info',
    'info'      => 'info',
    'notice'    => 'info',
    'warning'   => 'warning',
    'error'     => 'danger',
    'critical'  => 'danger',
    'alert'     => 'danger',
    'emergency' => 'danger',
    'processed' => 'info',
  ];

  private static $levels_imgs = [
    'debug'     => 'info',
    'info'      => 'info',
    'notice'    => 'info',
    'warning'   => 'warning',
    'error'     => 'warning',
    'critical'  => 'warning',
    'alert'     => 'warning',
    'emergency' => 'warning',
    'processed' => 'info',
  ];

  /**
   * Log levels that are used.
   *
   * @var array
   */
  private static $log_levels = [
    'emergency',
    'alert',
    'critical',
    'error',
    'warning',
    'notice',
    'info',
    'debug',
    'processed',
  ];

  public const MAX_FILE_SIZE = 52428800; // Why? Uh... Sorry

  /**
   * @param string $file
   */
  public static function setFile($file)
  {
    $file = self::pathToLogFile($file);

    if (app('files')->exists($file)) {
      self::$file = $file;
    }
  }

  /**
   * @param string $file
   *
   * @throws \Exception
   *
   * @return string
   */
  public static function pathToLogFile($file)
  {
    $logsPath = storage_path('logs');

    if (app('files')->exists($file)) { // try the absolute path
      return $file;
    }

    $file = $logsPath.'/'.$file;

    // check if requested file is really in the logs directory
    if (dirname($file) !== $logsPath) {
      throw new \Exception('No such log file');
    }

    return $file;
  }

  /**
   * @return string
   */
  public static function getFileName()
  {
    return basename(self::$file);
  }

  /**
   * @return array
   */
  public static function all()
  {
    $log = [];

    $pattern = '/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\].*/';

    if (!self::$file) {
      $log_file = self::getFiles();
      if (!count($log_file)) {
        return [];
      }
      self::$file = $log_file[0];
    }

    if (app('files')->size(self::$file) > self::MAX_FILE_SIZE) {
      return;
    }

    $file = app('files')->get(self::$file);

    preg_match_all($pattern, $file, $headings);

    if (!is_array($headings)) {
      return $log;
    }

    $log_data = preg_split($pattern, $file);

    if ($log_data[0] < 1) {
      array_shift($log_data);
    }

    foreach ($headings as $h) {
      for ($i = 0, $j = count($h); $i < $j; $i++) {
        foreach (self::$log_levels as $level) {
          if (strpos(strtolower($h[$i]), '.'.$level) || strpos(strtolower($h[$i]), $level.':')) {
            preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\](?:.*?(\w+)\.|.*?)'.$level.': (.*?)( in .*?:[0-9]+)?$/i', $h[$i], $current);
            if (!isset($current[3])) {
              continue;
            }

            $log[] = [
              'context'     => $current[2],
              'level'       => $level,
              'level_class' => self::$levels_classes[$level],
              'level_img'   => self::$levels_imgs[$level],
              'date'        => $current[1],
              'text'        => $current[3],
              'in_file'     => $current[4] ?? null,
              'stack'       => preg_replace("/^\n*/", '', $log_data[$i]),
            ];
          }
        }
      }
    }

    return array_reverse($log);
  }

  /**
   * @param bool $basename
   *
   * @return array
   */
  public static function getFiles($basename = false)
  {
    $files = glob(storage_path().'/logs/*.log');
    $files = array_reverse($files);
    $files = array_filter($files, 'is_file');
    if ($basename && is_array($files)) {
      foreach ($files as $k => $file) {
        $files[$k] = basename($file);
      }
    }

    return array_values($files);
  }
}

