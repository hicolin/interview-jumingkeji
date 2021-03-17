<?php

class DomainAnalysis
{
    public $domain;
    public $domainMain;

    // 所有的拼音集合
    public $pinYinArr= [
        "a","ai","an","ang","ao",
        "ba","bai","ban","bang","bao","be","bei","ben","beng","bi","bian","biao","bie","bin","bing","bo","bu",
        "ca","cai","can","cang","cao","ce","cen","ceng","cha","chai","chan","chang","chao","che","chen","cheng","chi","chong","chou","chu","chua","chuai","chuan","chuang","chui","chun","chuo","ci","cong","cou","cu","cuan","cui","cun","cuo",
        "da","dai","dan","dang","dao","de","dei","dem","den","deng","deo","di","dia","dian","diao","die","dim","ding","diu","dong","dou","du","duan","dug","dui","dun","duo",
        "e","ei","en","eng","er","fa","fan","fang","fei","fen","feng","fiao","fo","fou","fu","fui",
        "ga","gai","gan","gang","gao","ge","gei","gen","geng","go","gong","gongli","gou","gu","gua","guai","guan","guang","gui","gun","guo",
        "ha","hai","hal","han","hang","hao","he","hei","hen","heng","ho","hong","hou","hu","hua","huai","huan","huang","hui","hun","huo",
        "ji","jia","jian","jiang","jiao","jie","jin","jing","jiong","jiu","jou","ju","juan","jue","jun",
        "ka","kai","kan","kang","kao","ke","ken","keng","ki","kong","kou","ku","kua","kuai","kuan","kuang","kui","kun","kuo",
        "la","lai","lan","lang","lao","le","lei","leng","li","lia","lian","liang","liao","lie","lin","ling","liu","lo","long","lou","lu","luan","lue","lun","luo","lv",
        "m","ma","mai","man","mang","mao","me","mei","men","meng","meo","mi","mian","miao","mie","min","ming","miu","mo","mol","mou","mu",
        "na","nai","nan","nang","nao","ne","nei","nem","nen","neng","ni","nian","niang","niao","nie","nin","ning","niu","nong","nou","nu","nuan","nue","nun","nung","nuo","nv",
        "o","ou",
        "pa","pai","pan","pang","pao","pei","pen","peng","pi","pian","piao","pie","pin","ping","po","pou","pu","qi","qia","qian","qiang","qiao","qie","qin","qing","qiong","qiu","qu","quan","que","qun",
        "ra","ran","rang","rao","re","ren","reng","ri","rong","rou","ru","ruan","rui","run","ruo",
        "sa","sai","san","sang","sao","se","sei","sen","seng","seo","sha","shai","shan","shang","shao","she","shen","sheng","shi","shou","shu","shua","shuai","shuan","shuang","shui","shun","shuo","si","so","song","sou","su","suan","sui","sun","suo",
        "ta","tai","tan","tang","tao","te","teng","ti","tian","tiao","tie","ting","tong","tou","tu","tuan","tui","tun","tuo",
        "wa","wai","wan","wang","wei","wen","weng","wo","wu",
        "xi","xia","xian","xiang","xiao","xie","xin","xing","xiong","xiu","xu","xuan","xue","xun",
        "ya","yan","yang","yao","ye","yen","yi","yin","ying","yo","yong","you","yu","yuan","yue","yun",
        "za","zai","zan","zang","zao","ze","zei","zen","zeng","zha","zhai","zhan","zhang","zhao","zhe","zhen","zheng","zhi","zhong","zhou","zhu","zhua","zhuai","zhuan","zhuang","zhui","zhun","zhuo","zi","zo","zong","zou","zu","zuan","zui","zun","zuo"
    ];

    public function __construct($domain)
    {
        header("Content-Type: text/html;charset=utf-8");
        set_time_limit(0);

        $this->domain = $domain;
        $this->domainMain = explode('.', $domain)[0];

        echo "<pre>";
        echo "域名主体：" . $this->domainMain . PHP_EOL;
    }

    // 运行
    public function run()
    {
        $this->isNumeric();

        $this->isCharacter();

        $this->isZaMi();

        $this->checkPinYin();
    }

    // 数字
    public function isNumeric()
    {
        if (is_numeric($this->domainMain)) {
            echo '数字域名' . PHP_EOL;
        } else {
            echo '非数字域名' . PHP_EOL;
        }
    }

    // 字母
    public function isCharacter()
    {
        if (preg_match("/^[a-z]+$/", $this->domainMain)) {
            echo '字母域名' . PHP_EOL;
        } else {
            echo '非字母域名' . PHP_EOL;
        }
    }

    // 杂米
    public function isZaMi()
    {
        if (preg_match("/(?=.*\d+)(?=.*[a-z]+){2,63}/", $this->domainMain)) {
            echo '杂米域名' . PHP_EOL;
        } else {
            echo '非杂米域名' . PHP_EOL;
        }
    }

    // 拼音检测
    public function checkPinYin()
    {
        $isPinYin = true;
        $count = 0;

        while($this->domainMain != '' && $this->domainMain) {
            $match = '';

            foreach ($this->pinYinArr as $pinyin) {
                if ($this->domainMain == $pinyin) {
                    $match = $pinyin;
                    break;
                } else if (strpos($this->domainMain, $pinyin) === 0) {
                    $match = $pinyin;
                }
            }

            if ($match == '') {
                $isPinYin = false;
                break;
            }

            $this->domainMain = preg_replace('/^' . $match . '/', '', $this->domainMain);
            $count++;
        }

        if (!$isPinYin) {
            echo '非拼音域名' . PHP_EOL;
        } else {
            echo "拼音域名：{$count} 拼" . PHP_EOL;
        }
    }

}

$domain = "alibaba.com";
$da = new DomainAnalysis($domain);
$da->run();
