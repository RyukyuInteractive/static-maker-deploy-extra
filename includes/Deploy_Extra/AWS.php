<?php

namespace Static_Maker\Deploy_Extra;


class AWS
{
    private $file;
    private $path;
    private $option;
    private $bucket;
    private $region;
    private $s3_sync_option;
    private $distribution_id;

    public function __construct(File $file, Path $path, Option $option)
    {
        $this->file = $file;
        $this->path = $path;
        $this->option = $option;

        $option = $this->option->get_option();
        $this->region = $option['s3_region'];
        $this->bucket = 's3://' . $option['s3_bucket'];
        $this->s3_sync_option = $option['s3_sync_option'];
        $this->distribution_id = $option['distribution_id'];

        //set aws cli config
        putenv("AWS_DEFAULT_REGION=" . $this->region);
        putenv("AWS_ACCESS_KEY_ID=" . getenv('AWS_ACCESS_KEY_ID'));
        putenv("AWS_SECRET_ACCESS_KEY=" . getenv('AWS_SECRET_ACCESS_KEY'));

    }

    /**
     * S3同期処理
     * @param $timestamp
     * @return array
     */
    public function s3_sync_remote($timestamp)
    {
        $revision_path = $this->path->get_revision_path($timestamp);

        $option = $this->s3_sync_option;
        $aws_cli_command = "aws s3 sync {$option} --exact-timestamps --delete {$revision_path} {$this->bucket} --acl public-read 2>&1";
        exec($aws_cli_command, $out, $code);

        return [
            'output' => $out,
            'code' => $code,
        ];
    }

    /**
     * S3本番データダウンロード
     * @return array|false
     */
    public function s3_download_production_data()
    {
        $local_path = $this->path->get_local_production_path();

        if (!$local_path) {
            if (!$this->file->create_dir($this->path->get_local_production_path(false))) {
                return false;
            }
            $local_path = $this->path->get_local_production_path();
        }

        $option = $this->s3_sync_option;
        $aws_cli_command = "aws s3 sync {$option} --exact-timestamps --delete {$this->bucket} {$local_path} 2>&1";
        exec($aws_cli_command, $out, $code);

        return [
            'output' => $out,
            'code' => $code,
        ];
    }

    /**
     * S3のbucket名を取得
     *
     * @return string
     */
    public function get_bucket() {
        return $this->bucket;
    }

    /**
     * 指定するファイルパス、CloudFrontのキャッシュをAWS CLIで削除する
     *
     * @param $path
     * @return array|false
     */
    public function clear_cloudfront_cache($path) {
        $aws_cli_command = "aws cloudfront create-invalidation --distribution-id {$this->distribution_id} --paths {$path}";
        exec($aws_cli_command, $out, $code);

        return [
            'output' => $out,
            'code' => $code,
        ];
    }
}
