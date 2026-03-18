<?php

abstract class WooMailerLiteAbstractJob
{
    private $serial = true;

    protected static $delay = 0;

    public static $jobModel;

    protected $retryDelay = 10;

    protected $maxRetries = 3;

    protected $resourceLimit = 100;

    abstract public function handle($data = []);

    public static function getInstance()
    {
        return new static();
    }

    public static function dispatch(array $data = []): void
    {
        $jobClass = static::class;
        $objectId = 0;

        $data['attempts'] = $data['attempts'] ?? 0;

        if ((!isset($data['selfMechanism']['sync']) || !$data['selfMechanism']['sync']) && function_exists('as_enqueue_async_action')) {
            $objectId = as_enqueue_async_action($jobClass, [$data]);
            WooMailerLiteCache::set('scheduled_jobs', true, 300);
        }

        static::$jobModel = WooMailerLiteJob::updateOrCreate(
            ['job' => $jobClass],
            ['object_id' => $objectId, 'data' => $data]
        );

        if (isset($data['selfMechanism']['sync'])) {
            static::getInstance()->runSafely($data);
        }
    }

    public static function dispatchSync(array $data = []): void
    {
        $data['selfMechanism']['sync'] = true;
        static::dispatch($data);
    }

    public function runSafely($data = [])
    {
        try {
            if (!static::$jobModel) {
                static::$jobModel = WooMailerLiteJob::where('job', static::class)->first();
            }

            $this->handle($data);

            if (static::$jobModel) {
                static::$jobModel->delete();
            }
            WooMailerLiteCache::delete('scheduled_jobs');
            return true;

        } catch (Throwable $th) {
            WooMailerLiteCache::delete('scheduled_jobs');
            WooMailerLiteLog()->error("Failed Job: " . static::class, [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);

            $attempts = $data['attempts'] ?? 0;
            $attempts++;

            if (static::$jobModel) {
                static::$jobModel->update([
                    'data' => array_merge($data, [
                        'status' => 'failed',
                        'error' => $th->getMessage(),
                        'attempts' => $attempts,
                    ])
                ]);
            }

            if ($attempts < $this->maxRetries) {
                $data['attempts'] = $attempts;

                if (!isset($data['selfMechanism']['sync']) && function_exists('as_enqueue_async_action')) {
                    as_enqueue_async_action(static::class, [$data]);
                }
            } else {
                WooMailerLiteLog()->error("Job " . static::class . " failed after max retries.", ['trace' => $th->getTraceAsString()]);
            }
        }
        return true;
    }

    public static function delay(int $delay)
    {
        static::$delay = $delay;
        return new static();
    }
}
