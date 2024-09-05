<?php
namespace Helm\BrandFilterHide\Controller\Index;

use Magento\Framework\App\Http\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class Index implements ActionInterface
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @param Context $httpContext
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $httpContext,
        JsonFactory $resultJsonFactory
        )
    {
        $this->httpContext         = $httpContext;
        $this->resultJsonFactory   = $resultJsonFactory;
    }
    /**
     * @return json
     */
    public function execute()
    {
        $isLogin =  $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData(["response"=>$isLogin]);
    }
}