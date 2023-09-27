<?php
/**
 * 推送评论通知 支持server酱 telegram bot等推送模式
 * 
 * @package CommentPush
 * @author 黑弩
 * @version 1.0
 * @link https://www.heinu.cc
 */
class CommentPush_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
    
        Typecho_Plugin::factory('Widget_Feedback')->comment = array('CommentPush_Plugin', 'sc_send');
        Typecho_Plugin::factory('Widget_Feedback')->trackback = array('CommentPush_Plugin', 'sc_send');
        Typecho_Plugin::factory('Widget_XmlRpc')->pingback = array('CommentPush_Plugin', 'sc_send');
        
        return _t('请配置此插件的 SCKEY, 以使您的微信推送生效');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $model = new Typecho_Widget_Helper_Form_Element_Radio('model', array(0 => 'server酱', 1 => 'telegram bot'), 0, _t('选择一个推送模式'));
        $form->addInput($model);
    
        $key = new Typecho_Widget_Helper_Form_Element_Text('sckey', NULL, NULL, _t('KEY'), _t('server酱模式:<br />KEY 需要在 <a href="http://sctapi.ftqq.com/">Server酱</a> 中获取<br />telegram bot模式下:<br />填写bot的token'));
        $form->addInput($key->addRule('required', _t('您必须填写一个正确的 SCKEY')));

        $chatid = new Typecho_Widget_Helper_Form_Element_Text('chatid', NULL, NULL, _t('用户ID'), _t('telegram bot模式下接收推送的对话ID(发送至私聊为用户ID 群组ID请加-100)'));
        $form->addInput($chatid);
        
        $element = new Typecho_Widget_Helper_Form_Element_Radio('show', array(0 => '不显示', 1 => '显示'), 0, _t('推送是否显示评论内容和昵称(防止推送色情 暴力 政治敏感评论)'));
        $form->addInput($element);

    }
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    /**
     * 微信推送
     * 
     * @access public
     * @param array $comment 评论结构
     * @param Typecho_Widget $post 被评论的文章
     * @return void
     */
    public static function sc_send($comment, $post)
    {
        $options = Typecho_Widget::widget('Widget_Options');

        $sckey = $options->plugin('CommentPush')->sckey;
        
        $chatid = $options->plugin('CommentPush')->chatid;

        $text = "有人在您的博客发表了评论";


    if (Helper::options()->plugin('CommentPush')->model == 0) {
        if (Helper::options()->plugin('CommentPush')->show == 1) {
            $desp = "**".$comment['author']."** 在 [「".$post->title."」](".$post->permalink." \"".$post->title."\") 中发送了评论 内容:<br/> > ".$comment['text'];
        } else {
            $strlen = mb_strlen($comment['author'], 'utf-8');
            
            $firstStr = mb_substr($comment['author'], 0, 1, 'utf-8');
            
            $lastStr = mb_substr($comment['author'], -1, 1, 'utf-8');
            
            $user_name = $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($comment['author'], 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;

            $desp = "**".$user_name."** 在 [「".$post->title."」](".$post->permalink." \"".$post->title."\") 中发送了评论";
        }
    } elseif (Helper::options()->plugin('CommentPush')->model == 1) {
        if (Helper::options()->plugin('CommentPush')->show == 1) {
            $desp = "**".$comment['author']."** 在 [".$post->title."](".$post->permalink.") 中发送了评论 内容:`\n ".$comment['text']."`";
        } else {
            $strlen = mb_strlen($comment['author'], 'utf-8');
            
            $firstStr = mb_substr($comment['author'], 0, 1, 'utf-8');
            
            $lastStr = mb_substr($comment['author'], -1, 1, 'utf-8');
            
            $user_name = $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($comment['author'], 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;

            $desp = "**".$user_name."** 在 [".$post->title."](".$post->permalink.") 中发送了评论";
        }
    }


    if (Helper::options()->plugin('CommentPush')->model == 0) {
        $postdata = http_build_query(
            array(
                'text' => $text,
                'desp' => $desp
                )
            );

        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, 'https://sctapi.ftqq.com/'.$sckey.'.send');
        
        curl_setopt($ch, CURLOPT_POST, 1);
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $result = curl_exec($ch);
        
        curl_close($ch);

    } elseif (Helper::options()->plugin('CommentPush')->model == 1) {
    
    $postdata = http_build_query(
        array(
            'chat_id' => $chatid,
            'text' => $desp,
            'parse_mode' => 'Markdown'
            )
        );

        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot'.$sckey.'/sendMessage');
        
        curl_setopt($ch, CURLOPT_POST, 1);
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $result = curl_exec($ch);
        
        curl_close($ch);

    } else {}
        return  $comment;
    }
}
