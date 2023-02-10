<?

class ExcelReader
{
    private static function getSheet(string $filePath)
    {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

        $reader->setLoadSheetsOnly("Sheet1");
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($filePath);

        return $worksheet = $spreadsheet->getActiveSheet();
    }

    public static $prices = [];
    public static $mainArray = [];

    public static function getPrices(string $filePath)
    {
        $worksheet = ExcelReader::getSheet($filePath);

        $row = 2;

        while (
            $keyPrice = $worksheet->getCellByColumnAndRow(1, $row)->getValue() and
            $valuePrice = $worksheet->getCellByColumnAndRow(2, $row)->getValue()
        ) {
            ExcelReader::$prices[$keyPrice] = $valuePrice;
            $row++;
        }
    }

    public static function getData(string $filePath)
    {
        $worksheet = ExcelReader::getSheet($filePath);

        $highestColumn = $worksheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        for ($col = 4; $col <= $highestColumnIndex; ++$col) {
            if ($type = $worksheet->getCellByColumnAndRow($col, 1)->getValue()) {
                ExcelReader::$mainArray[$type] = array();

                for ($subCol = $col; $subCol <= $col + 2; ++$subCol) {
                    $row = 3;

                    array_push(ExcelReader::$mainArray[$type], array());

                    while ($worksheet->getCellByColumnAndRow($subCol, $row)->getValue() !== NULL) {
                        $value = $worksheet->getCellByColumnAndRow($subCol, $row)->getValue();

                        array_push(ExcelReader::$mainArray[$type][$subCol - $col], $value);

                        $row++;
                    }
                }
            }
        }
    }

    public static function canBuy($candidates, $params, $wanted)
    {
        $sum = [];
        print_r($params);
        for ($i = 0; $i < count($params); $i++) {
            $sum[$i] = $wanted[$i] - $params[$i];
        }

        $result = [];
        $subList = [];

        ExcelReader::doNext(0, $result, 0, $candidates, $sum[0], $sum[1], $sum[2], $subList);
        $result = ExcelReader::sort($result);

        return $result;
    }

    private static function doNext(
        int $i,
        &$result,
        int $count,
        $candidates,
        $target1,
        $target2,
        $target3,
        $subArr
    )
    {
        if ($target1 == 0 && $target2 == 0 && $target3 == 0) {
            $subList = [];

            for ($k = 0; $k < $count; $k++)
                array_push($subList, $subArr[$k]);

            array_push($result, $subList);
        } else if ($target1 > 0 && $target2 > 0 && $target3 > 0 && $count < 6) {
            foreach ($candidates as $key => $value) {
                for ($j = $i, $l = count($value[0]); $j < $l; $j++) {
                    $subArr[] = [
                        'type' => $key,
                        $value[0][$j],
                        $value[1][$j],
                        $value[2][$j]
                    ];

                    ExcelReader::doNext(
                        $j,
                        $result, $count + 1,
                        $candidates,
                        $target1 - $value[0][$j], $target2 - $value[1][$j], $target3 - $value[2][$j],
                        $subArr
                    );

                    unset($subArr[count($subArr) - 1]);
                    $subArr = array_values($subArr);
                }
            }
        }
    }

    private static function sort_func($firstElement, $secondElement)
    {
        if (
            ($firstElement['type'] == 'Legendary' and $secondElement['type'] == 'Epic')
            or ($firstElement['type'] == 'Legendary' and $secondElement['type'] == 'Rare')
            or ($firstElement['type'] == 'Legendary' and $secondElement['type'] == 'Uncommon')
            or ($firstElement['type'] == 'Legendary' and $secondElement['type'] == 'Common')
            or ($firstElement['type'] == 'Epic' && $secondElement['type'] == 'Rare')
            or ($firstElement['type'] == 'Epic' && $secondElement['type'] == 'Uncommon')
            or ($firstElement['type'] == 'Epic' && $secondElement['type'] == 'Common')
            or ($firstElement['type'] == 'Rare' && $secondElement['type'] == 'Uncommon')
            or ($firstElement['type'] == 'Rare' && $secondElement['type'] == 'Common')
            or ($firstElement['type'] == 'Uncommon' && $secondElement['type'] == 'Common')
        )
            return 1;
        elseif (
            $firstElement['type'] == $secondElement['type']
        ) {
            return 0;
        } else {
            return -1;
        }
    }

    private static function sort($unsorted)
    {
        for ($i = 0; $i < count($unsorted); $i++) {
            uasort($unsorted[$i], array('ExcelReader', 'sort_func'));
        }
        static::addPriceInUnsortedElements($unsorted);
        uasort($unsorted, ['ExcelReader', 'sortByPrice']);
        return $unsorted;
    }

    private static function sortByPrice($firstElement, $secondElement)
    {
        if ($firstElement['price'] > $secondElement['price']) {
            return 1;
        } elseif ($firstElement['price'] < $secondElement['price']) {
            return -1;
        } elseif ($firstElement['price'] == $secondElement['price']) {
            return 0;
        }
    }
    private static function addPriceInUnsortedElements(&$unsortedWithPrice)
    {
        foreach ($unsortedWithPrice as $kitKey => $kit) {
            $price = 0;
            foreach ($kit as $item) {
                $price += static::$prices[$item['type']];
            }
            $unsortedWithPrice[$kitKey]['price'] = $price;
        }
    }
}