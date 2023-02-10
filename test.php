<?php
/*public class CombinationSum {
  public static void main(String[] args) throws Exception {
    int[] candidates = {2, 3, 6, 7, 5, 2};

    List<List<Integer>> result = new CombinationSum().combinationSum(candidates, 12);
    System.out.print(result);
  }

  public List<List<Integer>> combinationSum(int[] candidates, int target) {
    List<List<Integer>> result = new ArrayList<>();
    List<Integer> subList = new ArrayList<>();
    doNext(0, result, 0, candidates, target, subList);
    System.out.print(result.size());
    return result;
  }

  private void doNext(
      int i,
      List<List<Integer>> result,
      int count,
      int[] candidates,
      int target,
      List<Integer> subArr) {
    if (target == 0) {
      List<Integer> subList = new ArrayList<>();
      for (int k = 0; k < count; k++) subList.add(subArr.get(k));
      result.add(subList);
    } else if (target > 0) {
      for (int j = i, l = candidates.length; j < l; j++) {
        subArr.add(candidates[j]);
        doNext(j, result, count + 1, candidates, target - candidates[j], subArr);
        subArr.remove(subArr.size() - 1);
      }
    }
  }
}*/

main();

function main() {
        $candidates = [2, 3, 6, 7, 5];
        $result = combinationSum($candidates, 7);
    }
function combinationSum($candidates, int $target) {
        $result = [];
        $subList = [];
        doNext(0, $result, 0, $candidates, $target, $subList);
        return $result;
    }
function doNext(
        int $i,
        &$result,
        int $count,
        $candidates,
        int $target,
        $subArr) {
      if ($target == 0) {
        $subList = [];
        for ($k = 0; $k < $count; $k++) array_push($subList, $subArr[$k]);
        $result[] = $subList;
        print_r($result);
      } else if ($target > 0) {
        for ($j = $i, $l = count($candidates); $j < $l; $j++) {
          array_push($subArr, $candidates[$j]);
          doNext($j, $result, $count + 1, $candidates, $target - $candidates[$j], $subArr);
          unset($subArr[count($subArr) - 1]);
          $subArr = array_values($subArr);
        }
      }
    }
?>