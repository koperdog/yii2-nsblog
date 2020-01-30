<?php

namespace koperdog\yii2nsblog\repositories;
use koperdog\yii2nsblog\models\{
    CategorySearch,
    Category,
    CategoryContent,
    CategoryContentQuery
};

/**
 * Description of CategoryRepository
 *
 * @author Koperdog <koperdog@github.com>
 * @version 1.0
 */
class CategoryRepository {
    
    private $searchModel = null;
        
    public function getSearchModel(): ?CategorySearch
    {
        return $this->searchModel;
    }
    
    public function get(int $id, $domain_id = null, $language_id = null): Category
    {
        
//        debug(CategoryContentQuery::getId($id, $domain_id, $language_id)->createCommand()->rawSql);
        
        $model = Category::find()
            ->with(['categoryContent' => function($query) use ($id, $domain_id, $language_id){
                $query->andWhere(['id' => CategoryContentQuery::getId($id, $domain_id, $language_id)->one()]);
            }])
            ->andWhere(['category.id' => $id])
            ->one();
                   
        if(!$model){
            throw new \DomainException("Category with id: {$id} was not found");
        }
        
        return $model;
    }
    
    public function getParents(Category $model): ?array
    {
        $parents = $model->parents()->all(); // offset depth shift 
        array_shift($parents);
        return $parents;
    }
    
    public function getParentNodeById(int $id): Category
    {
        if(!$model = Category::findOne($id)->parents(1)->one()){
            throw new \DomainException("Category have not parents");
        }
        
        return $model;
    }
    
    public function getParentNode(Category $model): Categor
    {
        if(!$model = $model->parents(1)->one()){
            throw new \DomainException("Category have not parents");
        }
        
        return $model;
    }
    
    public function search(array $params = [], $domain_id = null, $language_id = null): \yii\data\BaseDataProvider
    {
        $this->searchModel = new CategorySearch();
        $dataProvider = $this->searchModel->search($params, $domain_id, $language_id);
        
        return $dataProvider;
    }
    
    public function save($model): bool
    {
        if(!$model->save()){
            throw new \RuntimeException('Error saving model');
        }
        
        return true;
    }
    
    public function saveContent(CategoryContent $model, $domain_id = null, $language_id = null): bool
    {
        if(($model->domain_id != $domain_id || $model->language_id != $language_id) && $model->getDirtyAttributes())
        {
            return $this->copyCategoryContent($model, $domain_id, $language_id);
        }
        
        return $this->save($model);
    }
    
    public function link(string $name, $target, $model): void
    {
        $model->link($name, $target);
    }
    
    public function appendTo(Category $model): bool
    {
        $parent = $this->get($model->parent_id);
        
        if(!$model->appendTo($parent)){
            throw new \RuntimeException("Error append model");
        }
        
        return true;
    }
    
    public function setPosition(Category $model, Category $parentNode): bool
    {
        if(!$model->appendTo($parentNode, false)){
            throw new \RuntimeException('Error saving model');
        }
        
        return true;
    }
    
    public function getByPath(string $path): ?Category
    {
        $sections = explode('/', $path);
        
        $category = Category::find()
                ->where(['url' => array_shift($sections), 'depth' => Category::OFFSET_ROOT])
                ->one();
        
        $offset = Category::OFFSET_ROOT + 1; // +1 because array shift from sections
        
        foreach($sections as $key => $section){
            if($category){
                $category = $category->children(1)->where(['url' => $section, 'depth' => $key + $offset])->one();
            }
        }
        
        return $category;
    }
    
    public function delete(Categroy $model): bool
    {
        if ($model->isRoot()){
            $model->deleteWithChildren();
        }
        else{
            $model->delete();
        }
    }
    
    public static function getAll($exclude = null): ?array
    {
        return Category::find()
            ->joinWith(['categoryContent' => function($query){
                return CategoryContentQuery::getAll();;
            }])
            ->select(['category.id', 'category_content.name'])
            ->andWhere(['NOT IN', 'category.id', 1])
            ->andFilterWhere(['NOT IN', 'category.id', $exclude])
            ->all();
    }
    
    private function copyCategoryContent(\yii\db\ActiveRecord $model, $domain_id, $language_id)
    {
        $newContent = new CategoryContent();
        $newContent->attributes = $model->attributes;
        
        $newContent->domain_id   = $domain_id;
        $newContent->language_id = $language_id;
        
        return $this->save($newContent);
    }
}
