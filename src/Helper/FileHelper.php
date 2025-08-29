<?php

/**
 * Platine Framework
 *
 * Platine Framework is a lightweight, high-performance, simple and elegant
 * PHP Web framework
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2020 Platine Framework
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace Platine\Framework\Helper;

use Platine\Config\Config;
use Platine\Filesystem\Filesystem;
use Platine\Http\UploadedFile;
use Platine\Lang\Lang;
use Platine\Upload\Exception\UploadException;
use Platine\Upload\File\UploadFileInfo;
use Platine\Upload\Storage\FileSystem as UploadFileSystem;
use Platine\Upload\Upload;
use Platine\Upload\Validator\Rule\Extension;
use Platine\Upload\Validator\Rule\MimeType;
use Platine\Upload\Validator\Rule\Required;
use Platine\Upload\Validator\Rule\Size;
use Platine\Upload\Validator\RuleInterface;

/**
 * @class FileHelper
 * @package Platine\Framework\Helper
 * @template T
 */
class FileHelper
{
    /**
     *
     * @param Config<T> $config
     * @param Filesystem $filesystem
     * @param Lang $lang
     */
    public function __construct(
        protected Config $config,
        protected Filesystem $filesystem,
        protected Lang $lang,
    ) {
    }

    /**
     * Whether the file is uploaded
     * @param string $name
     * @return bool
     */
    public function isUploaded(string $name): bool
    {
        $uploadedFiles = UploadedFile::createFromGlobals();
        $uploadFile = $uploadedFiles[$name] ?? null;

        return $uploadFile instanceof UploadedFile
                && $uploadFile->getClientMediaType() !== null;
    }

    /**
     * Delete the given uploaded file in some situation, like after upload error
     * or can not save the information in database, etc.
     * @param UploadFileInfo $info
     * @return void
     */
    public function deleteUploadFile(UploadFileInfo $info): void
    {
        $file = $this->filesystem->file($info->getPath());
        if ($file->exists()) {
            $file->delete();
        }
    }

    /**
     * Whether the given file exist
     * @param string $filename
     * @param string|null $folder
     * @param bool $useRoot
     * @return bool
     */
    public function exists(string $filename, ?string $folder = null, bool $useRoot = true): bool
    {
        $configPath = $this->getRootPath(
            $this->config->get('platform.data_attachment_path'),
            $useRoot
        );

        $path = $configPath;
        if ($folder !== null) {
            $path .= DIRECTORY_SEPARATOR . $folder;
        }

        $filepath = sprintf('%s/%s', $path, $filename);
        $handle = $this->filesystem->get($filepath);

        return $handle !== null && $handle->exists();
    }

    /**
     * Delete the given file
     * @param string $filename
     * @param string|null $folder
     * @param bool $useRoot
     * @return bool
     */
    public function delete(string $filename, ?string $folder = null, bool $useRoot = true): bool
    {
        $configPath = $this->getRootPath(
            $this->config->get('platform.data_attachment_path'),
            $useRoot
        );
        $path = $configPath;
        if ($folder !== null) {
            $path .= DIRECTORY_SEPARATOR . $folder;
        }

        $filepath = sprintf('%s/%s', $path, $filename);
        $handle = $this->filesystem->get($filepath);
        if ($handle === null) {
            return true; //no need to delete if file does not exist
        }

        $handle->delete();

        return true;
    }

    /**
     * Delete the given upload public image file
     * @param string $filename
     * @param bool $useRoot
     * @return bool
     */
    public function deleteUploadPublicImage(string $filename, bool $useRoot = true): bool
    {
        return $this->handleDeleteUploadImage(
            $filename,
            'platform.public_image_path',
            $useRoot
        );
    }

    /**
     * Delete the given upload image file
     * @param string $filename
     * @param bool $useRoot
     * @return bool
     */
    public function deleteUploadImage(string $filename, bool $useRoot = true): bool
    {
        return $this->handleDeleteUploadImage(
            $filename,
            'platform.data_image_path',
            $useRoot
        );
    }

    /**
     * Upload an image
     * @param string $name
     * @param bool $useRoot
     * @return UploadFileInfo
     */
    public function uploadImage(string $name, bool $useRoot = true): UploadFileInfo
    {
        return $this->doUploadImage(
            $name,
            'platform.data_image_path',
            $useRoot
        );
    }

    /**
     * Upload an public image
     * @param string $name
     * @param bool $useRoot
     * @return UploadFileInfo
     */
    public function uploadPublicImage(string $name, bool $useRoot = true): UploadFileInfo
    {
        return $this->doUploadImage(
            $name,
            'platform.public_image_path',
            $useRoot
        );
    }

    /**
     * Upload an attachment
     * @param string $name
     * @param null|string $folder
     * @param bool $useRoot
     * @return UploadFileInfo
     */
    public function uploadAttachment(
        string $name,
        ?string $folder = null,
        bool $useRoot = true
    ): UploadFileInfo {
        $configPath = $this->getRootPath(
            $this->config->get('platform.data_attachment_path'),
            $useRoot
        );
        $path = $configPath;

        if ($folder !== null) {
            $directory = $this->filesystem->directory($configPath);
            $path .= DIRECTORY_SEPARATOR . $folder;
            // Create the folder if it does not exist
            if ($this->filesystem->directory($path)->exists() === false) {
                $directory->create($folder, 0775, true);
            }
        }

        $rules = [
            new Required(),
            new Size($this->getAttachmentMaxSize()),
        ];

        $extensions = $this->getAttachmentExtensionRules();
        if (count($extensions) === 0) {
            $extensions = [
                    'png',
                    'gif',
                    'jpg',
                    'jpeg',
                    'csv',
                    'txt',
                    'docx',
                    'doc',
                    'pdf',
                    'xls',
                    'xlsx',
                    'pptx',
                    'ppt',
                    'zip',
                    'json',
                ];
        }
        $rules[] = new Extension($extensions);

        $mimes = $this->getAttachmentMimeRules();
        if (count($mimes) > 0) {
            $rules[] = new MimeType($mimes);
        }


        return $this->doUpload(
            $name,
            $rules,
            $path,
        );
    }

    /**
     * Return the path with the root suffix
     * @param string $configPath
     * @param bool $useRoot whether to use root path
     * @return string
     */
    public function getRootPath(string $configPath, bool $useRoot = true): string
    {
        $rootId = '';
        if ($useRoot) {
            $rootId = $this->getRootPathId();
        }

        $path = sprintf(
            '%s%s%s',
            $configPath,
            DIRECTORY_SEPARATOR,
            $rootId
        );

        $directory = $this->filesystem->directory($configPath);

        // Create the folder if it does not exist
        if ($this->filesystem->directory($path)->exists() === false) {
            $directory->create($rootId, 0775, true);

            // if it's public path add index file to prevent directory listing
            $this->filesystem->directory($path)->createFile('index.html', 'Access denied');
        }

        return $path;
    }

    /**
     * Process the upload
     * @param string $name
     * @param RuleInterface[] $rules
     * @param string $path
     *
     * @return UploadFileInfo
     */
    protected function doUpload(
        string $name,
        array $rules,
        string $path,
    ): UploadFileInfo {
        $uploadedFiles = UploadedFile::createFromGlobals();
        /** @var UploadedFile $uploadFile */
        $uploadFile = $uploadedFiles[$name] ?? [];

        $upload = new Upload(
            $name,
            new UploadFileSystem(
                $path,
                true
            ),
            null,
            [$name => $uploadFile]
        );

        $upload->setFilename(md5(uniqid() . time()));

        $upload->addValidations($rules);

        if ($upload->isUploaded() === false) {
            throw new UploadException($this->lang->tr('The file to upload is empty'));
        }

        $isUploaded = $upload->process();
        if ($isUploaded === false) {
            $errors = $upload->getErrors();
            throw new UploadException($errors[0]);
        }

        /** @var UploadFileInfo $info */
        $info = $upload->getInfo();

        return $info;
    }

    /**
     * Upload an image
     * @param string $name
     * @param string $path
     * @param bool $useRoot
     *
     * @return UploadFileInfo
     */
    protected function doUploadImage(
        string $name,
        string $path,
        bool $useRoot = true
    ): UploadFileInfo {
        $configPath = $this->getRootPath($this->config->get($path), $useRoot);
        return $this->doUpload(
            $name,
            [
                new Size($this->getImageMaxSize()),
                new Extension(['png', 'gif', 'jpg', 'jpeg']),
                new MimeType([
                    'image/png',
                    'image/jpg',
                    'image/gif',
                    'image/jpeg',
                ])
            ],
            $configPath
        );
    }

    /**
     * Delete the given upload image file
     * @param string $filename
     * @param string $cfgPath
     * @param bool $useRoot
     * @return bool
     */
    protected function handleDeleteUploadImage(
        string $filename,
        string $cfgPath,
        bool $useRoot = true
    ): bool {
        $configPath = $this->getRootPath($this->config->get($cfgPath), $useRoot);
        $path = $configPath;

        $filepath = sprintf('%s/%s', $path, $filename);
        $handle = $this->filesystem->get($filepath);
        if ($handle === null) {
            return true; //no need to delete if file does not exist
        }

        $handle->delete();

        return true;
    }


    /**
     * Return the identifier of root path
     * @return string
     */
    protected function getRootPathId(): string
    {
        return '';
    }


    /**
     * Return the validation mime type rules of attachment upload
     * @return string[]
     */
    protected function getAttachmentMimeRules(): array
    {
        return ['text/plain'];
    }

    /**
     * Return the validation extension rules of attachment upload
     * @return string[]
     */
    protected function getAttachmentExtensionRules(): array
    {
        return [];
    }

    /**
     * Return the validation max file size rules of attachment upload
     * @return int|string
     */
    protected function getAttachmentMaxSize(): int|string
    {
        return '2M';
    }

    /**
     * Return the image max file size value
     * @return int|string
     */
    protected function getImageMaxSize(): int|string
    {
        return '1M';
    }
}
