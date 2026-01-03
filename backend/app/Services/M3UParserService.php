<?php

namespace App\Services;

use SplFileObject;
use Generator;
use InvalidArgumentException;

class M3UParserService
{
    private const MAX_CHANNELS = 3000;

    /**
     * 解析M3U文件并返回频道数据生成器
     *
     * @param string $filePath
     * @return Generator
     * @throws InvalidArgumentException
     */
    public function parse(string $filePath): Generator
    {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException('File does not exist');
        }

        $file = new SplFileObject($filePath);
        $file->setFlags(SplFileObject::DROP_NEW_LINE | SplFileObject::SKIP_EMPTY);

        // 验证M3U文件头
        $firstLine = $file->fgets();
        if (trim($firstLine) !== '#EXTM3U') {
            throw new InvalidArgumentException('Invalid M3U file format. Missing #EXTM3U header');
        }

        $currentChannel = null;
        $channelCount = 0;

        while (!$file->eof()) {
            $line = $file->fgets();
            
            if ($line === false) {
                continue;
            }

            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            // 解析 #EXTINF 行
            if (str_starts_with($line, '#EXTINF:')) {
                $currentChannel = $this->parseExtinf($line);
            }
            // 非注释行即为URL
            elseif (!str_starts_with($line, '#')) {
                if ($currentChannel) {
                    $channelCount++;
                    
                    // 限制频道数量
                    if ($channelCount > self::MAX_CHANNELS) {
                        throw new InvalidArgumentException(
                            "M3U file contains more than " . self::MAX_CHANNELS . " channels. Please split the file."
                        );
                    }

                    $currentChannel['stream_url'] = $line;
                    
                    // 验证URL
                    if ($this->isValidUrl($line)) {
                        yield $currentChannel;
                    }
                    
                    $currentChannel = null;
                }
            }
        }
    }

    /**
     * 解析 #EXTINF 行提取频道信息
     *
     * @param string $line
     * @return array
     */
    private function parseExtinf(string $line): array
    {
        // 提取所有属性 (key="value" 格式)
        preg_match_all('/(\w+(?:-\w+)*)="([^"]*)"/', $line, $matches, PREG_SET_ORDER);
        
        $attributes = [];
        foreach ($matches as $match) {
            $key = str_replace('-', '_', $match[1]); // 转换 tvg-id 为 tvg_id
            $attributes[$key] = $match[2];
        }

        // 提取频道名称 (最后一个逗号之后的内容)
        preg_match('/,(.+)$/', $line, $nameMatch);
        $name = isset($nameMatch[1]) ? trim($nameMatch[1]) : 'Unknown Channel';

        // 移除名称中可能的特殊字符
        $name = $this->sanitizeChannelName($name);

        return [
            'name' => $name,
            'tvg_id' => $attributes['tvg_id'] ?? null,
            'tvg_name' => $attributes['tvg_name'] ?? null,
            'tvg_logo' => $attributes['tvg_logo'] ?? null,
            'logo_url' => $attributes['tvg_logo'] ?? null,
            'group_title' => $attributes['group_title'] ?? 'Uncategorized',
            'country' => $attributes['tvg_country'] ?? null,
            'language' => $attributes['tvg_language'] ?? null,
            'category' => $attributes['group_title'] ?? null,
        ];
    }

    /**
     * 清理频道名称
     *
     * @param string $name
     * @return string
     */
    private function sanitizeChannelName(string $name): string
    {
        // 移除不必要的空格
        $name = preg_replace('/\s+/', ' ', $name);
        
        // 限制长度
        if (mb_strlen($name) > 255) {
            $name = mb_substr($name, 0, 255);
        }

        return trim($name);
    }

    /**
     * 验证URL是否有效
     *
     * @param string $url
     * @return bool
     */
    private function isValidUrl(string $url): bool
    {
        // 基本URL验证
        if (empty($url)) {
            return false;
        }

        // 支持 http/https/rtmp/rtsp 等协议
        $validProtocols = ['http://', 'https://', 'rtmp://', 'rtmps://', 'rtsp://'];
        
        foreach ($validProtocols as $protocol) {
            if (str_starts_with($url, $protocol)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 统计M3U文件中的频道数量（不解析全部内容）
     *
     * @param string $filePath
     * @return int
     */
    public function countChannels(string $filePath): int
    {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException('File does not exist');
        }

        $count = 0;
        $file = new SplFileObject($filePath);
        $file->setFlags(SplFileObject::DROP_NEW_LINE | SplFileObject::SKIP_EMPTY);

        while (!$file->eof()) {
            $line = trim($file->fgets());
            
            // 非注释行且非空行即为URL
            if (!empty($line) && !str_starts_with($line, '#')) {
                $count++;
            }

            // 提前终止统计以提高性能
            if ($count > self::MAX_CHANNELS) {
                return $count;
            }
        }

        return $count;
    }

    /**
     * 获取最大允许的频道数量
     *
     * @return int
     */
    public static function getMaxChannels(): int
    {
        return self::MAX_CHANNELS;
    }
}
